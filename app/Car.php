<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class Car extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'cars';
    }

    public function getAll()
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find();
    }

    public function getCarByCarId(string $car_id)
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find(array('car_id' => $car_id), array('limit' => 1))->toArray();
    }

    public function insertCar(string $car_id, string $category, string $discount)
    {
        Mongo::get()->{$this->connection}->{$this->collection}->replaceOne(array('car_id' => $car_id), array('car_id' => $car_id, 'category' => $category, 'discount' => $discount), array('upsert' => TRUE));
    }

}
