<?php

namespace App\Http\Controllers;

use App\Exports\MenuExport;
use App\Imports\MenuImport;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MenuController extends Controller
{
    public function get(Request $request)
    {
        $row        = $request->input('row');
        $keyword    = $request->input('keyword');
        $sortby     = $request->input('sortby');
        $sorttype   = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = Menu::orderBy('menus.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('menus.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('menus.path', 'LIKE', '%' . $keyword . '%');;
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'menus data has been retrieved',
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
            'path'         => 'required',
        ]);

        try {
            DB::beginTransaction();

            $menu = new Menu;
			$menu->name      = $request->input('name');
            $menu->path      = $request->input('path');
            $menu->save();

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'menu berhasil ditambahkan!',
                'data' => $menu
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat menambahkan data menu',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required',
            'path'         => 'required',
        ]);

        try {
            DB::beginTransaction();

            $menu = Menu::find($id);

            if ($menu) {
                $menu->name      = $request->input('name');
                $menu->path      = $request->input('path');
                $menu->save();

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'Data menu berhasil di update!',
                    'data' => $menu
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
            $menu = Menu::findOrFail($id);
            
            if (!$menu->delete()) {
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
            Excel::import(new MenuImport, request()->file('file'));
            return response()->json([
                'status' => 201,
                'message' => 'data berhasil di import!'
            ], 201);
        } catch (\Throwable $th) {
            \Sentry\captureException($th);
            return response()->json([
                'status' => 500,
                'message' => 'data gagal di import! ' . $th->getMessage()
            ], 500);
        }
    }

    public function selectedDelete(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = Menu::whereIn('id', $request->input('data'));
            if ($selected_delete->delete()) {
                $response = [
                    'status' => 200,
                    'message' => 'Menu data has been deleted',
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
            $selected_delete = Menu::whereIn('id', $request->input('data'))->select(
                'name', 'path'
            )->get();
            Excel::store(new MenuExport($selected_delete), 'Menu.xlsx');
        return response()->download(storage_path("app/Menu.xlsx"), "Menu.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
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
            $lp2b = Menu::whereIn('id', $request->input('data'))->get();
            if ($lp2b) {
                $response = [
                    'status' => 200,
                    'message' => 'Menu data has been retrieved',
                    'data' => $lp2b
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating Menu data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
