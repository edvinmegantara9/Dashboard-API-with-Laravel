<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\DocumentTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentTypesController extends Controller
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

            $documentType = DocumentTypes::orderBy('document_types.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('document_types.name', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);


            if ($documentType) {
                $response = [
                    'status' => 200,
                    'message' => 'document type data has been retrieved',
                    'data' => $documentType
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving document type data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required',
            ]
        );

        try {

            $documentType = DocumentTypes::create([
                'name' => $request->input('name'),
            ]);

            if ($documentType) {
                $response = [
                    'status' => 201,
                    'message' => 'document type data has been created',
                    'data' => $documentType
                ];
                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating document type data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'name' => 'required'
            ]
        );

        try {
            $documentType = DocumentTypes::find($id);
            $documentType->name = $request->input('name');
            $documentType->save();

            if ($documentType) {
                $response = [
                    'status' => 200,
                    'message' => 'document type data has been updated',
                    'data' => $documentType
                ];
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating document type data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $documentType = DocumentTypes::findOrFail($id);
            Documents::where('document_type', $id)->delete();

            if (!$documentType->delete()) {
                $response = [
                    'status' => 400,
                    'message' => 'document type data not found',
                ];
                return response()->json($response, 400);
            }
            DB::commit();

            $response = [
                'status' => 200,
                'message' => 'document type data has been deleted'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting document type data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
