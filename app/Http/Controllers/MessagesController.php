<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachments;
use App\Models\Messages;
use App\Models\MessageReceivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
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

            $message = Messages::with(['user', 'sender', 'receivers'])->orderBy('messages.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('messages.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('messages.content', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);


            if ($message) {
                $response = [
                    'status' => 200,
                    'message' => 'message data has been retrieved',
                    'data' => $message
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving message data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        $this->validate($request, [
            'title' => 'required|string',
            'content' => 'required',
            'sender_id' => 'required',
            'created_by' => 'required',
            'receivers' => 'required'
        ]);

        try {
            $message = Messages::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'sender_id' => $request->input('sender_id'),
                'created_by' => $request->input('created_by'),
            ]);

            if ($message) {
                $receivers = $request->input('receivers');
                $receivers = (array) json_decode($receivers); 
                foreach ($receivers as $receiver) {
                    MessageReceivers::create([
                        'receiver_id' => $receiver,
                        'is_read' => false,
                        'message_id' => $message->id
                    ]);
                }

                if ($request->input('attachments')) {
                    $attachments = $request->input('attachments');
                    foreach ($attachments as $attachment) {
                        MessageAttachments::create([
                            'message_id' => $message->id,
                            'file' => $attachment
                        ]);
                    }
                }

                DB::commit();
                $result = Messages::with(['user', 'sender', 'receivers'])->find($message->id);

                if ($result) {
                    $response = [
                        'status' => 201,
                        'message' => 'message data has been created',
                        'data' => $result
                    ];
                    return response()->json($response, 201);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating message data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'content' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $message = Messages::find($id);
            
            if($message)
            {
                $message->title = $request->input('title');
                $message->content = $request->input('content');
                if($request->input('receivers'))
                {
                    $receivers = $request->input('receivers');
                    $receivers = (array) json_decode($receivers); 
                    MessageReceivers::where('message_id', $id)->delete();
                    foreach ($receivers as $receiver) {
                        MessageReceivers::create([
                            'receiver_id' => $receiver,
                            'message_id' => $id
                        ]);
                    }
                }
                $message->save();

                $message->user;
                $message->sender;
                $message->receivers;

                $response = [
                    'status' => 200,
                    'message' => 'message data has been updated',
                    'data' => $message
                ];
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating message data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }


    public function delete($id)
    {

        try {
            DB::beginTransaction();
            $messages = Messages::findOrFail($id);

            if ($messages) {
                MessageReceivers::where('message_id', $id)->delete();
                MessageAttachments::where('message_id', $id)->delete();
            }



            if (!$messages->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'message data not found'
                ];

                return response()->json($response, 404);
            }

            DB::commit();

            $response = [
                'status' => 200,
                'message' => 'message has been deleted'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'status' => 400,
                'message' => 'error occured on deleting message',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
