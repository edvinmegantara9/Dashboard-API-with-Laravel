<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageAttachmentsController extends Controller
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
            $message_attachments = MessageAttachments::with(['message'])->orderBy('message_attachments.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('message_attachments.message.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('message_attachments.message.content', 'LIKE', '%' . $keyword . '%');
                })
                ->when($row, function($query) use ($row) {
                    return $query
                        ->when($row, function($query) use ($row) {
                    return $query
                        ->paginate($row);
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });


            if ($message_attachments) {
                $response = [
                    'status' => 200,
                    'message' => 'message attachment data has been retrieved',
                    'data' => $message_attachments
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving message attachment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
        
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'message_id' => 'required',
            'file' => 'required'
        ]);

        try {
            $message_attachments = MessageAttachments::create([
                'message_id' => $request->input('message_id'),
                'file' => $request->input('file')
            ]);

            if($message_attachments)
            {
                $response = [
                    'status' => 201,
                    'message' => 'message attachment data has been created',
                    'data' => $message_attachments
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating message attachment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $message_attachments = MessageAttachments::findOrFail($id);

            if(!$message_attachments->delete())
            {
                $response = [
                    'status' => 404,
                    'message' => 'message attachment data not found',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'message attachment data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting message attachment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }

    }
}
