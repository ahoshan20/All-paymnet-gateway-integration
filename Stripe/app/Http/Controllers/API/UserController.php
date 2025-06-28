<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users()
    {
        $users = User::get()->all();
        return response($users);
    }
    public function user(int $id)
    {
        $user = User::findOrFail($id);
        return response($user);
    }
}
