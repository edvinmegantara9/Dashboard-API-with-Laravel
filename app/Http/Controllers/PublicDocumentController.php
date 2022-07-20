<?php

namespace App\Http\Controllers;

use App\Models\PublicDocument;
use Illuminate\Http\Request;

class PublicDocumentController extends Controller
{
    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $type = $request->input('type');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        if ($type == 'null') $type = [];

        if (gettype($type) == 'string') $type = (array) json_decode($type);

        try {

            $publicDocuments = PublicDocument::with(['document_type'])->orderBy('public_documents.' . $sortby, $sorttype)
                ->whereIn('document_type', $type)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('public_documents.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhereHas('document_type', function ($query) use ($keyword) {
                            return $query
                            ->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                })
                ->paginate($row);


            if ($publicDocuments) {
                $response = [
                    'status' => 200,
                    'message' => 'public documents data has been retrieved',
                    'data' => $publicDocuments
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving documents data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'file' => 'required',
            'document_type' => 'required',
            'sub_document_type' => 'required',
            'image' => 'required',
            'tahun' => 'required'
        ]);

        try {
            $publicDocuments = PublicDocument::create([
                'title' => $request->input('title'),
                'file' => $request->input('file'),
                'document_type' => $request->input('document_type'),
                'sub_document_type' => $request->input('sub_document_type'),
                'image' => $request->input('image'),
                'tahun' => $request->input('tahun')
            ]);

            if ($publicDocuments) {
                $response = [
                    'status' => 201,
                    'message' => 'public documents data has been retrieved',
                    'data' => $publicDocuments
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating documents data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'file' => 'required',
            'document_type' => 'required',
            'sub_document_type' => 'required',
            'image' => 'required',
            'tahun' => 'required'
        ]);

        try {
            $publicDocuments = PublicDocument::find($id);

            if ($publicDocuments) {
                $publicDocuments->title = $request->input('title');
                $publicDocuments->file = $request->input('file');
                $publicDocuments->document_type = $request->input('document_type');
                $publicDocuments->sub_document_type = $request->input('sub_document_type');
                $publicDocuments->image = $request->input('image');
                $publicDocuments->tahun = $request->input('tahun');
                $publicDocuments->save();

                $response = [
                    'status' => 200,
                    'message' => 'public documents data has been updated',
                    'data' => $publicDocuments
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'public documents data not found',
            ];
            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating documents data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $publicDocuments = PublicDocument::findOrFail($id);

            if (!$publicDocuments->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'public documents data not found',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'public documents data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating documents data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
