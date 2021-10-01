<?php

namespace App\Http\Controllers;

use App\Models\Chats;
use App\Models\ChatsReceivers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatsController extends Controller
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

            $chat = Chats::with(['receivers'])->orderBy('chats.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('chats.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('chats.user.name', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);


            if ($chat) {
                $response = [
                    'status' => 200,
                    'message' => 'chat data has been retrieved',
                    'data' => $chat
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving chat data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'firebase_key' => 'required',
            'created_by' => 'required',
            'receivers' => 'required|array'
        ]);

        try {
            $chat = Chats::create([
                'title' => $request->input('title'),
                'firebase_key' => $request->input('firebase_key'),
                'start_chat' => Carbon::now(),
                'created_by' => $request->input('created_by')
            ]);

            if ($chat) {
                $receivers = $request->input('receivers');
                foreach ($receivers as $receiver) {
                    ChatsReceivers::create([
                        'role_id' => $receiver,
                        'chat_id' => $chat->id
                    ]);
                }

                $response = [
                    'status' => 200,
                    'message' => 'chat data has been created',
                    'data' => $chat
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating chat data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function endChat($id)
    {
        try {
            $chat = Chats::find($id);
            if ($chat) {
                $chat->end_chat = Carbon::now();
                $chat->save();

                $response = [
                    'status' => 200,
                    'message' => 'chat instance has been ended',
                    'data' => $chat
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'chat data not found',
            ];
            return response()->json($response, 404);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating chat data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $chat = Chats::findOrFail($id);
            
            if($chat)
            {
                ChatsReceivers::where('message_id', $id)->delete();
            }

            if(!$chat->delete())
            {
                $response = [
                    'status' => 404,
                    'message' => 'chat data not found',
                ];
                return response()->json($response, 404);
            }
            DB::commit();
            $response = [
                'status' => 200,
                'message' => 'chat data has been deleted',
            ];
            return response()->json($response, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting chat data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
