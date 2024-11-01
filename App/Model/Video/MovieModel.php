<?php
namespace App\Model\Video;
use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class MovieModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 't_movie';
    protected $primaryKey = 'id';

}