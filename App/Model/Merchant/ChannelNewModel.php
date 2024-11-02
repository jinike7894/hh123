<?php
namespace App\Model\Merchant;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class ChannelNewModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'ch_channel';

    protected $primaryKey = 'channelId';
   
}