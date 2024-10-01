<?php

namespace App\Model\Merchant;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class ChannelModel
 * @package App\Model\Merchant
 * @property $channelId int | 渠道id
 * @property $channelKey string | 渠道key
 * @property $channelDomain string | 渠道域名
 * @property $merchantId int | 商户id
 * @property $percentage int | 计量百分比
 * @property $cpaCost float | cpa单价
 * @property $coefficient float | 点击系数
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ChannelModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'ch_channel';

    protected $primaryKey = 'channelId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['channelId']) && $where['channelId'] = $keyword['channelId'];
        isset($keyword['channelKey']) && $where['channelKey'] = $keyword['channelKey'];
        isset($keyword['channelDomain']) && $where['channelDomain'] = $keyword['channelDomain'];
        isset($keyword['merchantId']) && $where['merchantId'] = $keyword['merchantId'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    /**
     * 检查Key是否已存在
     * @param $key
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function checkKeyExists($key)
    {
        if ($key) {
            return !!$this->where(['channelKey' => $key, 'status' => [self::STATE_DELETED, '>']])->val($this->primaryKey);
        } else {
            return false;
        }
    }

    /**
     * 检查域名是否已绑定
     * @param $domain
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function checkDomainExists($domain)
    {
        if ($domain) {
            return !!$this->where(['channelDomain' => $domain, 'status' => [self::STATE_DELETED, '>']])->val($this->primaryKey);
        } else {
            return false;
        }
    }
}