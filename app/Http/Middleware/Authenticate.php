<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    protected function authenticate($request, array $guards)
    {
        if (!$request->get('api_token') && $request->path() != 'login')
        {
            throw new \Illuminate\Auth\AuthenticationException(json_encode(array('status' => 'error', 'message' => 'Please login.')));
        }

        $userModel = new \App\User();
        $user = $userModel->findUserByApiToken(addslashes($request->get('api_token')));
        if (!(is_array($user) && count($user) > 0))
        {
            throw new \Illuminate\Auth\AuthenticationException(json_encode(array('status' => 'error', 'message' => 'Invalid user authentication')));
        }
    }

}
