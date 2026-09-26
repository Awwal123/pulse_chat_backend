<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use HttpResponses;

    public function login() {
        return 'This is my login Method';
    }

        public function register() {
        return response()->json('This is my register Method');
    }

       public function logout() {
        return response()->json('This is my logout Method');
    }
}