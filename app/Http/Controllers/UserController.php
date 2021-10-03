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
    
    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $users = Users::with(['users'])->orderBy('users.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('users.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.nip', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.position', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.group', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);

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
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }
}
