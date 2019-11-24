<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mongo;

class User extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->connection = 'parking';
        $this->collection = 'users';
    }

    public function getUserByUsername($username)
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find(array('username' => $username), array('limit' => 1))->toArray();
    }

    public function findUserByApiToken($api_token)
    {
        return Mongo::get()->{$this->connection}->{$this->collection}->find(array('api_token' => addslashes($api_token)), array('limit' => 1))->toArray();
    }

}
