<?php

namespace App\Http\Controllers;

use App\Models\Chats;
use App\Models\ChatsReceivers;
use App\Models\Documents;
use App\Models\MessageReceivers;
use App\Models\Messages;
use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\RolesOpds;
use App\Models\Users;
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
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $role = Roles::with(['opd'])->orderBy('roles.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('roles.name', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);

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

            DB::beginTransaction();
            $role = Roles::create(
                [
                    'name' => $request->input('name'),
                    'is_opd' => $request->input('is_opd')
                ]
            );

            if ($role) {
                if($request->input('opds'))
                {
                    $opds = (array) json_decode($request->input('opds'));
                    foreach ($opds as $opd) {
                        RolesOpds::create([
                            'role_id' => $role->id,
                            'opd_id' => $opd
                        ]);
                    }

                }

                DB::commit();
                $response = [
                    'status' => 201,
                    'message' => 'role has been created',
                    'data' => $role
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating role',
                'error' => $e->getMessage()
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
            DB::beginTransaction();
            $role = Roles::find($id);
            $role->name = $request->input('name');
            $role->is_opd = $request->input('is_opd');
            $role->save();

            if ($role) {
                RolesOpds::where('role_id', $id)->delete();

                if($request->input('opds'))
                {
                    if(gettype($request->input('opds')) == 'string')
                    $opds = (array) json_decode($request->input('opds'));
                    else
                    $opds = $request->input('opds');
                    foreach ($opds as $opd) {
                        RolesOpds::create([
                            'role_id' => $role->id,
                            'opd_id' => $opd
                        ]);
                    }

                }
                DB::commit();
                $response = [
                    'status' => 200,
                    'message' => 'role has been updated',
                    'data' => $role
                ];

                return response()->json($response, 200);
            } else {
                DB::rollBack();
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
            DB::beginTransaction();
            $role = Roles::findOrFail($id);

            RolesOpds::where('role_id', $id)->delete();
            RolesOpds::where('opd_id', $id)->delete();
            Documents::where('upload_by', $id)->delete();
            ChatsReceivers::where('role_id', $id)->delete();
            Chats::where('created_by', $id)->delete();
            MessageReceivers::where('receiver_id', $id)->delete();
            Messages::where('created_by', $id)->delete();
            Users::where('role_id', $id)->delete();

            if (!$role->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'role data not found'
                ];

                return response()->json($response, 404);
            }
            DB::commit();
            $response = [
                'status' => 200,
                'message' => 'role has been deleted'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting role',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
