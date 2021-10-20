<?php

namespace App\Http\Controllers;

use App\Models\MessageReceivers;
use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageReceiversController extends Controller
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
            $message = MessageReceivers::with(['receiver', 'message'])->orderBy('message_receivers.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('message_receivers.receiver.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('message_receivers.message.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('message_receivers.message.content', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);


            if ($message) {
                $response = [
                    'status' => 200,
                    'message' => 'message receiver data has been retrieved',
                    'data' => $message
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving message receiver data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function read_message(Request $request)
    {
        $this->validate($request, [
            'message_id' => 'required',
            'receiver_id' => 'required'
        ]);

        try {
            $message_receivers = MessageReceivers::where('message_id', $request->input('message_id'))
            ->where('receiver_id', $request->input('receiver_id'))->first();

            if($message_receivers)
            {
                $message_receivers->is_read = 1;
                $message_receivers->save();

                $response = [
                    'status' => 200,
                    'message' => 'message data has been read',
                    'data' => $message_receivers
                ];
                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'message receiver data not found',
            ];
            return response()->json($response, 404);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating message receiver data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'receiver_id' => 'required'
        ]);

        try {
            $message_receivers = MessageReceivers::find($id);
            if ($message_receivers) {
                $message_receivers->receiver_id = $request->input('receiver_id');
                $message_receivers->save();

                $response = [
                    'status' => 201,
                    'message' => 'message receiver data has been updated',
                    'data' => $message_receivers
                ];
                return response()->json($response, 201);
            }
            $response = [
                'status' => 404,
                'message' => 'message receiver data not found',
            ];
            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating message receiver data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        $message_receivers = MessageReceivers::findOrFail($id);
        if (!$message_receivers->delete()) {
            $response = [
                'status' => 404,
                'message' => 'message receiver data not found',
            ];
            return response()->json($response, 404);
        }

        $response = [
            'status' => 200,
            'message' => 'message receiver data has been deleted',
        ];
        return response()->json($response, 200);
    }
}
