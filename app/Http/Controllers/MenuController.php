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
}
