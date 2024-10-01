<?php

namespace App\Service\Navigation;

use App\Model\Navigation\AdTypeModel;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Utility\File;
use Vtiful\Kernel\Format;

class AdClickStatisticService
{
    use Singleton;

    public function exportTotalList($headers, $list, $fileName)
    {
        $config = Func::getExcelConfig();
        //创建文件夹
        File::createDirectory($config['path'], 0777);

        $excel = new \Vtiful\Kernel\Excel($config);

        $adTypeList = AdTypeModel::create()->where(['status' => AdTypeModel::STATE_NORMAL])->indexBy('adTypeId');

        // 处理xls需要的数据格式
        $data = [];
        $typeLength = [];
        $listCount = count($list);
        if (!empty($list)) {
            foreach ($list as $row) {
                $temp = [];
                foreach ($headers as $value) {
                    switch ($value[1]) {
                        case 'clickCount': // 想要进行数值计算必须转数字
                            $temp[] = isset($row[$value[1]]) ? intval($row[$value[1]]) : '';
                            break;
                        case 'adName':
                            $temp[] = $row[$value[1]] .'-备注：'. $row['remark'] ?? '';
                            break;
                        default:
                            $temp[] = $row[$value[1]] ?? '';
                    }
                }

                // 添加占位符，注意要按顺序来
                $temp[] = ''; // 总点击
                $temp[] = ''; // 产品点击比例
                // $temp[] = isset($adTypeList[$row['adTypeId']]['conversionRate']) ? $adTypeList[$row['adTypeId']]['conversionRate'] / 100 : 0; // 预计转化率

                // 记录类型长度
                if (isset($typeLength[$row['adTypeId']])) {
                    $typeLength[$row['adTypeId']]++;
                } else {
                    $typeLength[$row['adTypeId']] = 1;
                }

                $data[] = $temp;
            }
        }

        // 补充后续的表头
        $headers = array_merge(
            array_column($headers, '0'),
            ['总点击', '产品点击比例', '预计转化率', '预计激活注册']
        );


        $excel = $excel
            ->fileName($fileName)
            ->header($headers)
            ->data($data);

        /* 处理样式 begin */
        /* 合计 begin */
        $right = $listCount + 1;
        $excel = $excel->insertFormula($listCount + 1, 4, "=SUM(E2:E{$right})");
        $clickSumCell = Func::getExcelCellCoordinate($listCount + 1, 4);
        /* 合计 end */

        /* 总点击 产品点击比例 begin */
        $lineNumber = 2; // 第一行是表头，所以是从第二行开始。
        foreach ($typeLength as $item) {
            $right = $lineNumber + $item - 1;
            $totalClickMergeText = "F{$lineNumber}:F{$right}"; // 注意合并的行号
            $sumText = "E{$lineNumber}:E{$right}"; // 相加计算的行号

            $totalClickRateMergeText = "G{$lineNumber}:G{$right}";

            $excel = $excel
                ->mergeCells($totalClickMergeText, '')
                ->insertFormula($lineNumber - 1, 5, "=SUM($sumText)") // 这个5就是ABCDEF中F的索引
                ->mergeCells($totalClickRateMergeText, '') // 产品点击比例合并
                ->insertFormula($lineNumber - 1, 6, "=TEXT(F{$lineNumber}/{$clickSumCell},\"0.00%\")"); // 产品点击比例公式

            $lineNumber = $right + 1; //下一行又作为下一次合并的开始行。
        }
        /* 总点击 产品点击比例 end */

        /* 预计转化率，预计注册数 begin */
        for ($i = 1; $i <= $listCount; ++$i) {
            $lineNumber = $i + 1;
            $index = $i - 1;
            $conversionRate = $adTypeList[$list[$index]['adTypeId']]['conversionRate'] / 100;
            $excel = $excel
                ->insertFormula($i, 7, "=TEXT({$conversionRate},\"#%\")")
                ->insertFormula($i, 8, "=ROUNDUP(E{$lineNumber}*H{$lineNumber},0)");
        }
        /* 预计转化率，预计注册数 end */

        /* 处理表头样式 begin */
        $fileHandle = $excel->getHandle();
        $format = new \Vtiful\Kernel\Format($fileHandle);
        $headerStyle = $format->bold()
            ->fontColor(Format::COLOR_WHITE)
            ->background(0x4889f4) // 这个是颜色的十六进制数，这里写十进制的都行。
            ->align(Format::FORMAT_ALIGN_CENTER, Format::FORMAT_ALIGN_VERTICAL_CENTER)
            ->toResource();

        $excel = $excel->freezePanes(1, 0)
            ->setRow("A1", 30, $headerStyle);
        /* 处理表头样式 end */

        /* 处理数据行样式 begin */
        $fileHandle = $excel->getHandle();
        $format = new \Vtiful\Kernel\Format($fileHandle);
        $boldStyle = $format->bold()
            ->align(Format::FORMAT_ALIGN_CENTER, Format::FORMAT_ALIGN_VERTICAL_CENTER)
            ->toResource();

        $maxLine = $listCount + 2;
        $excel = $excel->setRow("A2:A{$maxLine}", 20, $boldStyle);
        /* 处理数据行样式 end */

        /* 处理列宽 begin */
        $excel = $excel->setColumn('B:B', 12); // 广告名
        $excel = $excel->setColumn('G:G', 13.88); // 产品点击比例
        $excel = $excel->setColumn('H:H', 11.25); // 预计转化率
        $excel = $excel->setColumn('I:I', 13.5); // 预计激活注册
        /* 处理列宽 begin */

        /* 处理样式 end */

        $filePath = $excel->output();

        // 关闭当前打开的所有文件句柄 并 回收资源
        $excel->close();

        return $filePath;
    }
}