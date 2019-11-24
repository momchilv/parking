<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class ParkingLog extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'parking_log';
    }

    public function insertLog($log)
    {
        Mongo::get()->{$this->connection}->{$this->collection}->insertOne($log);        
    }
}
