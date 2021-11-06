<?php

namespace App\Http\Controllers;

use App\Models\Chats;
use App\Models\ChatsReceivers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use function PHPUnit\Framework\isEmpty;

class ChatsController extends Controller
{

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $page = (int) $page;
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function history(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $role_id = $request->input('role_id');
        $page = $request->input('page');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        if ($page == 'null') $page = null;

        try {

            $chat = Chats::with(['receivers', 'user'])->where('created_by', $role_id)->orderBy('rooms.' . $sortby, $sorttype)
                ->whereNotNull('end_chat')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('rooms.room_name', 'LIKE', '%' . $keyword . '%');
                    // ->orWhereHas('user', function ($query) use ($keyword) {
                    //     return $query
                    //         ->where('name', 'LIKE', '%' . $keyword . '%');
                    // });
                })->get();

            $chat_receivers = ChatsReceivers::with(['room.user'])->where('role_id', $role_id)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->whereHas('room', function ($query) use ($keyword) {
                            return $query
                                ->whereNotNull('end_chat')
                                ->where('room_name', 'LIKE', '%' . $keyword . '%');
                        });
                    // ->orWhereHas('room.user', function ($query) use ($keyword) {
                    //     return $query
                    //         ->orWhere('name', 'LIKE', '%' . $keyword . '%');
                    // });
                })
                ->get();

            $data = [];
            $key = false;

            foreach ($chat as $chat_sender) {
                if ($data != [])
                    foreach ($data as $_data) {
                        $key = $chat->id == $_data->id;
                        if ($key)
                            break;
                    }
                        if(!$key)
                            array_push($data, $chat_sender);

                else
                    array_push($data, $chat_sender);
            }

            foreach ($chat_receivers as $chat_receiver) {
                if ($data != [])
                    foreach ($data as $_data) {
                        $key = $chat->id == $_data->id;
                        if ($key)
                            break;
                    }
                    if (!$key)
                            array_push($data, $chat_receiver->room);
                else
                    array_push($data, $chat_receiver->room);
            }

            if ($data != []) $data = $this->paginate($data, $row, $page);

            $items = $data->items();
            $data_fix = json_decode($data->toJson());
            $data_fix->data = array_values($items);


            if ($chat) {
                $response = [
                    'status' => 200,
                    'message' => 'chat data has been retrieved',
                    'data' => $data_fix
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

    public function get(Request $request)
    {
        // $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $role_id = $request->input('role_id');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {

            $chat = Chats::with(['receivers', 'user'])->where('created_by', $role_id)->orderBy('rooms.' . $sortby, $sorttype)
                // ->whereNull('end_chat')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('rooms.room_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhereHas('user', function ($query) use ($keyword) {
                            return $query
                                ->where('name', 'LIKE', '%' . $keyword . '%');
                        });
                })->get();

            $chat_receivers = ChatsReceivers::with(['room', 'room.user', 'room.receivers'])->where('role_id', $role_id)
                ->whereHas('room', function ($query) {
                    return $query->whereNull('end_chat');
                })
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->whereHas('room', function ($query) use ($keyword) {
                            return $query
                                ->whereNull('end_chat')
                                ->where('room_name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('room.user', function ($query) use ($keyword) {
                            return $query
                                ->orWhere('name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhereHas('room.receivers', function ($query) use ($keyword) {
                            return $query
                                ->orWhere('name', 'LIKE', '%' . $keyword . '%');
                        });
                })
                ->get();

            $data = [];
            $key = false;

            foreach ($chat as $chat_sender) {
                if (count($data) > 0)
                    foreach ($data as $_data) {
                        return $chat;
                        $key = $chat->id == $_data['id'];
                        if ($key)
                            break;
                    }
                        if(!$key)
                            array_push($data, $chat_sender);

                else
                    array_push($data, $chat_sender);
            }

            foreach ($chat_receivers as $chat_receiver) {
                if (count($data) > 0)
                    foreach ($data as $_data) {
                        $key = $chat->id == $_data['id'];
                        if ($key)
                            break;
                    }
                    if (!$key)
                            array_push($data, $chat_receiver->room);
                else
                    array_push($data, $chat_receiver->room);
            }

            if ($chat) {
                $response = [
                    'status' => 200,
                    'message' => 'chat data has been retrieved',
                    'data' => $data
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
            'room_name' => 'required',
            // 'room_id' => 'required',
            'created_by' => 'required',
            'receivers' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $chat = Chats::create([
                'room_name' => $request->input('room_name'),
                'room_id' => uniqid('silaper_room'),
                'start_chat' => Carbon::now(),
                'created_by' => $request->input('created_by')
            ]);

            if ($chat) {
                $receivers = $request->input('receivers');
                if (gettype($receivers) == 'string')
                    $receivers = (array) json_decode($receivers);
                foreach ($receivers as $receiver) {
                    ChatsReceivers::create([
                        'role_id' => $receiver,
                        'room_id' => $chat->id
                    ]);
                }
                DB::commit();

                $chat->user;
                $chat->receivers;

                foreach ($chat->receivers as $receiver) {
                    $receiver->room;
                }

                $response = [
                    'status' => 200,
                    'message' => 'chat data has been created',
                    'data' => $chat
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'status' => 400,
                'message' => 'error occured on creating chat data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function rateChat(Request $request, $id)
    {
        $this->validate($request, [
            'role_id' => 'required',
            'rating' => 'required'
        ]);

        $role_id = $request->input('role_id');
        $rating = $request->input('rating');

        try {
            $receiver = ChatsReceivers::where('role_id', $role_id)->where('room_id', $id)->first();

            if ($receiver) {
                $receiver->rating = $rating;
                $receiver->save();

                $response = [
                    'status' => 200,
                    'message' => 'chat instance has been rated',
                    'data' => $receiver
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

            if ($chat) {
                ChatsReceivers::where('room_id', $id)->delete();
            }

            if (!$chat->delete()) {
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
