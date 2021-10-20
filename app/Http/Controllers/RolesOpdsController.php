<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\RolesOpds;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class RolesOpdsController extends Controller
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
        $page = $request->input('page');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $opds = RolesOpds::with(['role','opd'])->orderBy('roles_opds.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('roles_opds.role.name', 'LIKE', '%' . $keyword . '%')
                        ->where('roles_opds.opd.name', 'LIKE', '%' . $keyword . '%');
                })->when($row, function($query) use ($row) {
                    return $query
                        ->paginate($row);
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });
                
            if ($opds) {
                $response = [
                    'status' => 200,
                    'message' => 'opd data has been retrieved',
                    'data' => $opds
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving opd data',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'role_id' => 'required',
            'opd_id' => 'required'
        ]);

        try {
            $opd = RolesOpds::create(
                [
                    'role_id' => $request->input('role_id'),
                    'opd_id' => $request->input('opd_id')
                ]
            );

            if ($opd) {
                $opd->role;
                $opd->opd;
                $response = [
                    'status' => 201,
                    'message' => 'opd has been created',
                    'data' => $opd
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating opd',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'role_id' => 'required',
            'opd_id' => 'required'
        ]);

        try {
            $opd = RolesOpds::find($id);
            $opd->role_id = $request->input('role_id');
            $opd->opd_id = $request->input('opd_id');
            $opd->save();

            if ($opd) {
                $opd->role;
                $opd->opd;
                $response = [
                    'status' => 200,
                    'message' => 'opd has been updated',
                    'data' => $opd
                ];

                return response()->json($response, 200);
            } else {
                $response = [
                    'status' => 404,
                    'message' => 'opd data not found'
                ];

                return response()->json($response, 404);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating opd',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $opd = RolesOpds::findOrFail($id);

            if (!$opd->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'opd data not found'
                ];

                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'opd has been deleted'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting opd',
                'error' => $e
            ];
            return response()->json($response, 400);
        }
    }
}
