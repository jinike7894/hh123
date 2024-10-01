<?php
namespace App\Model\CustomerService;
use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class CustomerServiceModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'customer_service';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'int';
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    const DELETE = 1;
    const NO_DELETE = 0;
}