<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class Price extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'prices';
    }

    public function getAll()
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find();
    }

}
