<?php

namespace App\Http\Controllers;

use App\Exports\RoleExport;
use App\Imports\RoleImport;
use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = Role::with('menus')->orderBy('roles.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('roles.name', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'roles data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving categorie data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
        ]);

        try {
            DB::beginTransaction();

            $role = new Role;
			$role->name      = $request->input('name');
            $role->save();

            if (count($request->get('role_menus')) > 0) {
                foreach ($request->get('role_menus') as $d) {
                    $detail = new RoleMenu;
                    $detail->role_id     = $role->id;
                    $detail->menu_id     = $d['menu_id'];
                    $detail->save();
                }
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'role berhasil ditambahkan!',
                'data' => $role
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat menambahkan data role',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::find($id);

            if ($role) {
                $role->name      = $request->input('name');
                $role->save();

                if (count($request->get('role_menus')) > 0) {
                    foreach ($request->get('role_menus') as $d) {
                        $detail = new RoleMenu;
                        $detail->role_id     = $role->id;
                        $detail->menu_id     = $d['menu_id'];
                        $detail->save();
                    }
                }

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'Data role berhasil di update!',
                    'data' => $role
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'Data tidak ditemukan!',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat mengubah data!',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $role = Role::findOrFail($id);
            
            if (!$role->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'Data tidak ditemukan!',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'Data berhasil dihapus!',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat menghapus Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function import() 
    {
        try {
            Excel::import(new RoleImport, request()->file('file'));
            return json_encode([
                'status' => 201,
                'message' => 'data berhasil di import!'
            ]);
        } catch (\Throwable $th) {
            \Sentry\captureException($th);
            return json_encode([
                'status' => 500,
                'message' => 'data gagal di import! ' . $th->getMessage()
            ]);
        }
    }

    public function selectedDelete(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = Role::whereIn('id', $request->input('data'));
            if ($selected_delete->delete()) {
                $response = [
                    'status' => 200,
                    'message' => 'Role data has been deleted',
                ];
    
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportExcel(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = Role::whereIn('id', $request->input('data'))->select(
                'name'
            )->get();
            Excel::store(new RoleExport($selected_delete), 'Role.xlsx');
        return response()->download(storage_path("app/Role.xlsx"), "Role.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportPdf(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $lp2b = Role::whereIn('id', $request->input('data'))->get();
            if ($lp2b) {
                $response = [
                    'status' => 200,
                    'message' => 'Role data has been retrieved',
                    'data' => $lp2b
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating Role data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
