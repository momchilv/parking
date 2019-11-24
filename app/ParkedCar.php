<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class ParkedCar extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'parked_cars';
    }

    public function getAll()
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find();
    }

    public function getCarByCarId(string $car_id)
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find(array('car_id' => $car_id), array('limit' => 1))->toArray();
    }

    public function insertParkedCar(string $car_id)
    {
        Mongo::get()->{$this->connection}->{$this->collection}->replaceOne(array('car_id' => $car_id), array('car_id' => $car_id, 'check_in_time' => time()), array('upsert' => TRUE));
    }

    public function deleteParkedCarByCarId(string $car_id)
    {
        Mongo::get()->{$this->connection}->{$this->collection}->deleteOne(array('car_id' => $car_id));
    }

}
