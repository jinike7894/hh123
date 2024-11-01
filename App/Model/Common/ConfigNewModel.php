<?php
namespace App\Model\Common;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\RedisPool\RedisPool;
use Exception;
class ConfigNewModel extends AbstractModel
{
    protected $tableName = 'config';

    protected $primaryKey = 'configId';
  
}