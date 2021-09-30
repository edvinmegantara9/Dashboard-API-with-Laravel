<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\RolesOpds;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class RoleController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api');
    }

    public function get(Request $request)
    {
        try {
            $role = Roles::with(['opd'])->get();
            if ($role) {
                $response = [
                    'status' => 200,
                    'message' => 'role data has been retrieved',
                    'data' => $role
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving role data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'is_opd' => 'required'
        ]);

        try {
            $role = Roles::create(
                [
                    'name' => $request->input('name'),
                    'is_opd' => $request->input('is_opd')
                ]
            );

            if ($role) {
                $response = [
                    'status' => 201,
                    'message' => 'role has been created',
                    'data' => $role
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating role',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'is_opd' => 'required'
        ]);

        try {
            $role = Roles::find($id);
            $role->name = $request->input('name');
            $role->is_opd = $request->input('is_opd');
            $role->save();

            if ($role) {
                $response = [
                    'status' => 200,
                    'message' => 'role has been updated',
                    'data' => $role
                ];

                return response()->json($response, 200);
            } else {
                $response = [
                    'status' => 404,
                    'message' => 'role data not found'
                ];

                return response()->json($response, 404);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating role',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $role = Roles::findOrFail($id);

            RolesOpds::where('role_id', $id)->delete();
            RolesOpds::where('opd_id', $id)->delete();

            if (!$role->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'role data not found'
                ];

                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'role has been deleted'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting role',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
