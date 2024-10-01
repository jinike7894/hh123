<?php

namespace App\Service\Navigation;

use App\Enum\ConfigKey\AppConfigKey;
use App\Enum\ConfigKey\OssConfigKey;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\WebsiteConfigKey;
use App\Model\Common\ConfigModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageTemplateModel;
use App\Model\Navigation\PageTemplateZoneRelationModel;
use App\RedisKey\Navigation\PageKey;
use App\RedisKey\Navigation\TemplateKey;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\File;
use Exception;
use Throwable;

class PageService
{
    use Singleton;

    const DATA_VERSION_1 = 1;
    const DATA_VERSION_2 = 2;

    public function addPage($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $page = PageModel::create()->where(['pageName' => $data['pageName']])->get();
            if ($page) {
                throw new Exception('该页面已存在', Status::CODE_BAD_REQUEST);
            }

            $pageId = PageModel::create($data)->save();

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $pageId;
    }

    public function editPage($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $page = PageModel::create()->get($data['pageId']);

            if (!$page) {
                throw new Exception('无效的页面id', Status::CODE_BAD_REQUEST);
            }

            if (isset($data['pageName']) && $data['pageName']) {
                $pageExists = PageModel::create()
                    ->where([
                        'pageName' => $data['pageName'],
                        'pageId' => [$data['pageId'], '!='],
                    ])
                    ->get();

                if ($pageExists) {
                    throw new Exception('该页面名称已存在', Status::CODE_BAD_REQUEST);
                }
            }

            // 这里有一个固定的，就是默认页面不能修改文件名
            if ($page->pageName == 'index.html' && isset($data['pageName']) && $data['pageName'] && $data['pageName'] != 'index.html') {
                throw new Exception('默认页面请勿修改名字', Status::CODE_BAD_REQUEST);
            }

            $result = $page->update($data);

            if ($result) {
                $this->deletePageCache($page->pageName);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * 获取页面视图数据
     * @param PageModel $page
     * @param int $dataVersion 数据版本 1.数据为数组 2.数据为对象
     * @return array
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getViewData(PageModel $page, $dataVersion = self::DATA_VERSION_1)
    {
        $template = PageTemplateModel::create()->getByCache($page->pageTemplateId);

        $templateData = $this->getTemplateData($page->pageTemplateId);

        // $config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_WEBSITE);
        $config = ConfigModel::create()->getConfigValueList(array_merge(
            WebsiteConfigKey::ALL_KEY,
            AppConfigKey::ALL_KEY,
            [OssConfigKey::AWS_S3_HOST]
        ));

        // 2023-10-26 将统计代码扣量的配置写到了每一个页面中
        $config['WebsiteStatisticEnabled'] = $page->statisticEnabled;
        $config['WebsiteStatisticConfig'] = $page->statisticConfig;

        if ($dataVersion == self::DATA_VERSION_2) {
            $templateData = array_column($templateData, null, 'zoneKey');
        }

        $appConfig = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_APP);
        // 接口域名需要转数组
        if (isset($appConfig[AppConfigKey::API_DOMAIN])) {
            $tempList = explode(';', $appConfig[AppConfigKey::API_DOMAIN]);

            $apiDomain = [];
            foreach ($tempList as $item) {
                $item = trim($item);
                $item && $apiDomain[] = $item;
            }

            $appConfig[AppConfigKey::API_DOMAIN] = $apiDomain;
        }

        return [
            'page' => $page->visible(['pageId', 'pageName', 'pageTemplateId', 'code','navCode']),
            'template' => $template->visible(['pageTemplateId', 'pageTemplateKey']),
            'config' => $config,
            'templateData' => $templateData,
            'appConfig' => $appConfig,
        ];
    }

    /**
     * 获取模板对应的广告数据
     * @param $pageTemplateId
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function getTemplateData($pageTemplateId)
    {
        $redis = RedisPool::defer();
        $key = TemplateKey::data($pageTemplateId);
        $data = $redis->get($key);

        if (!$data) {
            // step.1 先拿出广告位与广告组的配置
            // 这个广告位和广告组的对应列表查出来需要重新组合一下格式
            $zoneTempList = PageTemplateZoneRelationModel::create()->getTemplateZone($pageTemplateId, PageTemplateZoneRelationModel::STATE_NORMAL);

            // step.2 通过需要的广告组拿到所有广告
            $adGroupIdList = array_column($zoneTempList, 'adGroupId');

            $adList = AdModel::create()->getAllByGroup($adGroupIdList);
            $adGroupCollection = [];
            foreach ($adList as $adItem) {
                $temp = $adItem->toRawArray();
                // 因为给安卓和IOS提供接口，这里让有数据和没有数据的时候保持json_encode出来都是{}形式
                // 所以在decode的时候不传true来转成数组，保持对象形式。如果要处理数据请注意这里。
                // 注：php数组如果有数据json_encode是 {}，没有数据json_encode是[]
                // 如果是标准对象则json_encode 无论有没有数据都是 {}
                // 但是因为底层redis数据用的json编码，所以这里还是会被转成[]，哈哈哈哈。
                // 如果把redis编码改成SERIALIZE_PHP，那样是可以保持{}的，但是有些地方需要逻辑统一才能支持。目前统一都用的json。
                $temp['extension'] = json_decode($temp['extension'], true);
                $temp['adGroupId'] = $adItem['adGroupId'];

                if (isset($adGroupCollection[$adItem['adGroupId']])) {
                    $adGroupCollection[$adItem['adGroupId']][] = $temp;
                } else {
                    $adGroupCollection[$adItem['adGroupId']] = [$temp];
                }
            }

            // step.3 组装广告位->广告组->广告列表的数据列表
            $zoneList = [];
            foreach ($zoneTempList as $zoneItem) {
                $adGroup = [
                    'adGroupId' => $zoneItem['adGroupId'],
                    'sort' => $zoneItem['sort'],
                    'adGroupName' => $zoneItem['adGroupName'],
                    'adGroupAlias' => $zoneItem['adGroupAlias'],
                    'adGroupKey' => $zoneItem['adGroupKey'],
                    'adList' => $adGroupCollection[$zoneItem['adGroupId']] ?? [],
                ];

                if (isset($zoneList[$zoneItem['zoneId']])) {
                    $zoneList[$zoneItem['zoneId']]['adGroup'][] = $adGroup;
                } else {
                    $zoneList[$zoneItem['zoneId']] = [
                        'zoneId' => $zoneItem['zoneId'],
                        'adGroup' => [$adGroup],
                        'zoneName' => $zoneItem['zoneName'],
                        'zoneKey' => $zoneItem['zoneKey'],
                    ];
                }
            }

            $data = array_values($zoneList);

            $redis->set($key, $data, 600);
        }

        return $data;
    }

    /**
     * 删除模板数据缓存
     * @param array $pageTemplateIdList
     * @return false|string
     */
    public function deleteTemplateCache(array $pageTemplateIdList)
    {
        $redis = RedisPool::defer();

        $keyList = [];
        foreach ($pageTemplateIdList as $pageTemplateId) {
            $keyList[] = TemplateKey::data($pageTemplateId);
        }

        return $redis->del(...$keyList);
    }

    /**
     * 删除页面数据缓存
     * @param array $pageIdList
     * @return false|string
     */
    public function deleteConfigPageCache(array $pageIdList)
    {
        $redis = RedisPool::defer();

        $keyList = [];
        foreach ($pageIdList as $pageId) {
            $keyList[] = TemplateKey::pageConfigCache($pageId);
        }

        return $redis->del(...$keyList);
    }

    /**
     * 通过index.html生成页面文件
     * @param PageModel $page
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function create(PageModel $page)
    {
        $result = File::copyFile(SystemConfigKey::FRONTEND_PATH . '/index.html', SystemConfigKey::FRONTEND_PATH . '/' . $page->pageName);

        if (!$result) {
            throw new Exception('创建文件' . $page->pageName . '失败，请检查服务器。', Status::CODE_BAD_REQUEST);
        }

        $page->update(['latestTime' => date('Y-m-d H:i:s')]);

        return $result;
    }

    /**
     * 创建一个静态数据页面
     * @param PageModel $page
     * @return bool
     * @throws Exception
     */
    public function createStaticDataPage(PageModel $page)
    {

        $pageTemplateKey = PageTemplateModel::create()->where(['pageTemplateId' => $page->pageTemplateId])->val('pageTemplateKey');

        // 对应的模板有单独的模板页
        $templateFile = SystemConfigKey::FRONTEND_PATH . "/_template_{$pageTemplateKey}.html";

        // 如果没有定义单独模板页就取公共模板页
        if (!is_file($templateFile)) {
            $templateFile = SystemConfigKey::FRONTEND_PATH . '/_template.html';
        }

        if (!is_file($templateFile)) {
            throw new Exception('未找到模板文件_template.html，请检查服务器。', Status::CODE_BAD_REQUEST);
        }

        $content = file_get_contents($templateFile);
        $data = $this->getViewData($page);
        $content = str_replace('<DATA>', json_encode($data), $content);

        $result = File::createFile(SystemConfigKey::FRONTEND_PATH . '/' . $page->pageName, $content);

        if ($result) {
            $page->update(['latestTime' => date('Y-m-d H:i:s')]);
        }

        return $result;
    }

    /**
     * 删除页面缓存
     * @param $pageName
     */
    public function deletePageCache($pageName)
    {
        $key = PageKey::cache($pageName);
        $redis = RedisPool::defer();
        $redis->del($key);
    }
}