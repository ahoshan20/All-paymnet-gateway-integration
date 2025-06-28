<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function users()
    {
        $users = User::get()->all();
        return response($users);

    }
}
