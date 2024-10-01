<?php

namespace App\Service\Admin;

use App\Model\Video\ShortVideoModel;
use App\Model\Video\VideoModel;
use EasySwoole\Component\Singleton;

class UpdateDomainService
{
    use Singleton;

    public function updateVodUrl($oldHost, $newHost)
    {
        $data = VideoModel::create()->func(function ($builder) use ($oldHost, $newHost) {
            $tableName = VideoModel::create()->getTableName();
            $builder->raw("UPDATE `{$tableName}` SET `vod_play_url` = REPLACE(`vod_play_url`, ?, ?)", [$oldHost, $newHost]);
            return true;
        });
        return $data;
    }

    public function updateVodPic($oldHost, $newHost)
    {
        $data = VideoModel::create()->func(function ($builder) use ($oldHost, $newHost) {
            $tableName = VideoModel::create()->getTableName();
            $builder->raw("UPDATE `{$tableName}` SET `vod_pic_thumb` = REPLACE(`vod_pic_thumb`, ?, ?)", [$oldHost, $newHost]);
            return true;
        });

        VideoModel::create()->func(function ($builder) use ($oldHost, $newHost) {
            $tableName = VideoModel::create()->getTableName();
            $builder->raw("UPDATE `{$tableName}` SET `vod_pic2` = REPLACE(`vod_pic2`, ?, ?)", [$oldHost, $newHost]);
            return true;
        });

        return $data;
    }

    public function updateShortVodUrl($oldHost, $newHost)
    {
        $data = ShortVideoModel::create()->func(function ($builder) use ($oldHost, $newHost) {
            $tableName = ShortVideoModel::create()->getTableName();
            $builder->raw("UPDATE `{$tableName}` SET `vodPlayUrl` = REPLACE(`vodPlayUrl`, ?, ?)", [$oldHost, $newHost]);
            return true;
        });
        return $data;
    }


    public function updateShortVodPic($oldHost, $newHost)
    {
        $data = ShortVideoModel::create()->func(function ($builder) use ($oldHost, $newHost) {
            $tableName = ShortVideoModel::create()->getTableName();
            $builder->raw("UPDATE `{$tableName}` SET `vodPic` = REPLACE(`vodPic`, ?, ?)", [$oldHost, $newHost]);
            return true;
        });
        return $data;
    }
}