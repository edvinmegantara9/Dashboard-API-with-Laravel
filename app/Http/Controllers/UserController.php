<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
            $users = User::with('roles', 'restorant')->orderBy('users.' . $sortby, $sorttype)
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->where(function ($query) use ($keyword) {
                    return $query
                        ->where('users.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.nip', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('roles.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('users.email', 'LIKE', '%' . $keyword . '%');
                })
                ->select('users.*')
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
            \Sentry\captureException($e);
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
            $users = User::find($id);
            if ($users) {
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
            \Sentry\captureException($e);
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
            'full_name' => 'required',
            'nip'       => 'required',
            'email'     => 'required',
            'role_id'   => 'required',
        ]);

        try {
            $users = User::find($id);

            if ($users) {
                $users->full_name = $request->input('full_name');
                $users->nip       = $request->input('nip');
                $users->email     = $request->input('email');
                $users->role_id   = $request->input('role_id');
                $users->restorant_id = $request->input('restorant_id');
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
            \Sentry\captureException($e);
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
            $users = User::findOrFail($id);

            if ($users) {
                User::where('id', $id)->delete();
            }

            if (!$users->delete()) {
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
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting user data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedDelete(Request $request)
    {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = User::whereIn('id', $request->input('data'));

            if ($selected_delete->delete()) {
                $response = [
                    'status' => 200,
                    'message' => 'User data has been deleted',
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting user data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportExcel(Request $request)
    {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = User::whereIn('id', $request->input('data'))->select(
                'full_name',
                'email',
                'phone_number'
            )->get();
            Excel::store(new UserExport($selected_delete), 'User.xlsx');
            return response()->download(storage_path("app/User.xlsx"), "User.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on exporting users data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportPdf(Request $request)
    {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $product = User::whereIn('id', $request->input('data'))->get();
            if ($product) {
                $response = [
                    'status' => 200,
                    'message' => 'User data has been retrieved',
                    'data' => $product
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on exporting User data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function test()
    {
        return 'test';
    }
}
