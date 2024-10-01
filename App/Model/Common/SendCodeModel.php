<?php

namespace App\Model\Common;

use App\Model\BaseModel;

class SendCodeModel extends BaseModel
{
    protected $tableName = 'send_code';

    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const TYPE_MS = 'MS';
    const TYPE_EMAIL = 'Email';

    const CHANNEL_DEFAULT = 'Default';
    const CHANNEL_JSMS = 'JSMS';

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 0;

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['target']) && $where['target'] = $keyword['target'];
        isset($keyword['requestIpLong']) && $where['requestIpLong'] = $keyword['requestIpLong'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        }

        return $where;
    }
}