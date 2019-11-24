<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class Category extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'car_categories';
    }

    public function getAll()
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find();
    }

    public function getByCategory(string $category)
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find(array('category' => $category), array('limit' => 1))->toArray();
    }

}
