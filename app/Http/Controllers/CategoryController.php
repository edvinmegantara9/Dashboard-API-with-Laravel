<?php

namespace App\Http\Controllers;

use App\Exports\CategoryExport;
use App\Imports\CategoryImport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
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
            $data = Category::orderBy('categories.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('categories.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('categories.amount', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'categories data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
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
            'amount'       => 'required',
            'is_active'    => 'required',
        ]);

        try {
            DB::beginTransaction();

            $category = new Category;
			$category->name      = $request->input('name');
            $category->amount    = $request->input('amount');
            $category->is_active = $request->input('is_active');
            $category->save();

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'category data has been created',
                'data' => $category
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating category data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required',
            'amount'       => 'required',
            'is_active'    => 'required',
        ]);

        try {
            DB::beginTransaction();

            $category = Category::find($id);

            if ($category) {
                $category->name      = $request->input('name');
                $category->amount    = $request->input('amount');
                $category->is_active = $request->input('is_active');
                $category->save();

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'category data has been updated',
                    'data' => $category
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'category data not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating category data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            if (!$category->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'category data not found',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'category data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating category data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function import() 
    {
        try {
            Excel::import(new CategoryImport, request()->file('file'));
            return json_encode([
                'status' => 201,
                'message' => 'data berhasil di import!'
            ]);
        } catch (\Throwable $th) {
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
            $selected_delete = Category::whereIn('id', $request->input('data'));
            if ($selected_delete->delete()) {
                $response = [
                    'status' => 200,
                    'message' => 'Category data has been deleted',
                ];
    
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
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
            $selected_delete = Category::whereIn('id', $request->input('data'))->select(
                'name'
            )->get();
            Excel::store(new CategoryExport($selected_delete), 'Category.xlsx');
        return response()->download(storage_path("app/Category.xlsx"), "Category.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
        } catch (\Exception $e) {
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
            $lp2b = Category::whereIn('id', $request->input('data'))->get();
            if ($lp2b) {
                $response = [
                    'status' => 200,
                    'message' => 'Category data has been retrieved',
                    'data' => $lp2b
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating Category data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
