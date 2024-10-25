<?php
namespace App\Model\GameColumn;
use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\Model\Prostitute\ProstituteModel;


class GameColumn extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'game_column';

    protected $primaryKey = 'id';

   
}