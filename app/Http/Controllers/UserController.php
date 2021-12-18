<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $users = Users::with(['role'])->orderBy('users.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('users.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.nip', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.position', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.group', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

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


    public function changepassword(Request $request, $id)
    {
        $this->validate($request, [
            'password' => 'required'
        ]);

        try {
            $users = Users::find($id);
            if($users)
            {
                $users->password = app('hash')->make($request->input('password'));
                $users->save();

                $response = [
                    'status' => 200,
                    'message' => 'user password has been updated',
                    'data' => $users
                ];
    
                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'user data not found',
            ];
            return response()->json($response, 404);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating user data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // 'nip' => 'required|string|unique:users',,
            'email' => 'required',
            'full_name' => 'required',
            'position' => 'required',
            'group' => 'required',
            'role_id' => 'required'
        ]);

        try {
            $users = Users::find($id);

            if ($users) {
                // $users->nip      = $request->input('nip');
                $users->position = $request->input('position');
                $users->full_name = $request->input('full_name');
                $users->group    = $request->input('group');
                $users->email = $request->input('email');
                $users->role_id  = $request->input('role_id');
                $users->save();

                $response = [
                    'status' => 200,
                    'message' => 'user data has been updated',
                    'data' => $users
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'user data not found',
            ];
            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating user data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {

        try {
            DB::beginTransaction();
            $users = Users::findOrFail($id);

            if ($users) {
                Messages::where('sender_id', $id)->delete();
            }

            if(!$users->delete())
            {
                $response = [
                    'status' => 404,
                    'message' => 'user data not found',
                ];
                return response()->json($response, 404);
            }

            DB::commit();

            $response = [
                'status' => 200,
                'message' => 'user data has been deleted',
            ];

            return response()->json($response, 200);


        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting user data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }
}
