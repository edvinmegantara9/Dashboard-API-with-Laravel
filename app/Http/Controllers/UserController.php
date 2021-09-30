<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function get()
    {
        try {
            $users = Users::with(['role'])->get();

            foreach ($users as $user) {
                $user->role->opd;
            }

            if ($users) {
                $response = [
                    'status' => 200,
                    'message' => 'user data has been retrieved',
                    'data' => $users
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving user data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
