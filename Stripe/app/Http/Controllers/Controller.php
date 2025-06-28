<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    public function users()
    {
        $users = User::get()->all();
        return response($users);

    }
}
