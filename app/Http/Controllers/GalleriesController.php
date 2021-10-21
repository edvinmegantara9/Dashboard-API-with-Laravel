<?php

namespace App\Http\Controllers;

use App\Models\Galleries;
use Illuminate\Http\Request;

class GalleriesController extends Controller
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

            $galleries = Galleries::orderBy('galleries.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('galleries.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('galleries.file', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);


            if ($galleries) {
                $response = [
                    'status' => 200,
                    'message' => 'gallery data has been retrieved',
                    'data' => $galleries
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving gallery data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'file' => 'required'
        ]);

        try {
            $galleries = Galleries::create(
                [
                    'title' => $request->input('title'),
                    'file' => $request->input('file')
                ]
            );

            if ($galleries) {
                $response = [
                    'status' => 201,
                    'message' => 'gallery data has been created',
                    'data' => $galleries
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating gallery data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);

        try {
            $galleries = Galleries::find($id);

            if ($galleries) {
                $galleries->title = $request->input('title');
                if ($request->input('file'))
                    $galleries->file = $request->input('file');
                $galleries->save();

                $response = [
                    'status' => 200,
                    'message' => 'gallery data has been updated',
                    'data' => $galleries
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'gallery data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating gallery data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $galleries = Galleries::findOrFail($id);

            if (!$galleries->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'gallery data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'gallery data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating gallery data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
