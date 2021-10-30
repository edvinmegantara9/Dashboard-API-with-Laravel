<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\Roles;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class DocumentsController extends Controller
{
    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $role_id = $request->input('role_id');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $role = Roles::with(['opd'])->where('id', $role_id)->first();

            if(!$role)
            {
                $response = [
                    'status' => 404,
                    'message' => 'documents data not found',
                ];

                return response()->json($response, 404);
            }

            $is_opd = $role->is_opd;
            $opdIds = [];
            if ($is_opd == 0) {
                $opds = $role->opd;
                foreach ($opds as $opd) {
                    array_push($opdIds, $opd->id);
                }
            }

            $documents = Documents::with(['uploader', 'document_type'])->orderBy('documents.' . $sortby, $sorttype)
                ->when($is_opd == 1 && $role->name != 'ADMIN', function ($query) use ($role_id) {
                    return $query
                        ->where('documents.upload_by', $role_id);
                })
                ->when($is_opd == 0 && $role->name != 'ADMIN', function ($query) use ($opdIds) {
                    return $query
                        ->whereIn('documents.upload_by', $opdIds);
                })
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('documents.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhereHas('uploader', function ($query) use ($keyword) {
                            return $query
                            ->where('name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('document_type', function ($query) use ($keyword) {
                            return $query
                            ->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                })
                ->paginate($row);


            if ($documents) {
                $response = [
                    'status' => 200,
                    'message' => 'documents data has been retrieved',
                    'data' => $documents
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
        $this->validate(
            $request,
            [
                'title' => 'required',
                'file' => 'required',
                'upload_by' => 'required',
                'document_type' => 'required'
            ]
        );

        try {

            $documents = Documents::create([
                'title' => $request->input('title'),
                'file' => $request->input('file'),
                'upload_by' => $request->input('upload_by'),
                'document_type' => $request->input('document_type')
            ]);

            if ($documents) {
                $response = [
                    'status' => 201,
                    'message' => 'documents data has been created',
                    'data' => $documents
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
        $this->validate(
            $request,
            [
                'title' => 'required',
                'upload_by' => 'required',
                'document_type' => 'required'
            ]

        );

        try {
            $documents = Documents::find($id);
            $documents->title = $request->input('title');
            if ($request->input('file'))
                $documents->file = $request->input('file');
            $documents->upload_by = $request->input('upload_by');
            $documents->document_type = $request->input('document_type');
            $documents->save();

            if ($documents) {
                $response = [
                    'status' => 200,
                    'message' => 'documents data has been updated',
                    'data' => $documents
                ];
                return response()->json($response, 200);
            }
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
            $documents = Documents::findOrFail($id);
            if (!$documents->delete()) {
                $response = [
                    'status' => 400,
                    'message' => 'documents data not found',
                ];
                return response()->json($response, 400);
            }
            $response = [
                'status' => 200,
                'message' => 'documents data has been deleted'
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting documents data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
