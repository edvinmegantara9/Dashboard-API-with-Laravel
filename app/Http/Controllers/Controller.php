<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function respondWithToken($token, $user)
    {
        return response()->json([
            'status' => 200,
            'message' => 'login successful',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => null,
            'user' => $user
        ], 200);
    }
}
