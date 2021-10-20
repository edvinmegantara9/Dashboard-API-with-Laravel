<?php

namespace App\Http\Controllers;

use App\Models\MessageAttachments;
use App\Models\Messages;
use App\Models\MessageReceivers;
use App\Models\Roles;
use App\Models\RolesOpds;
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

            $message = Messages::with(['user', 'sender', 'receivers', 'attachments'])->orderBy('messages.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('messages.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('messages.content', 'LIKE', '%' . $keyword . '%');
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
                if (gettype($receivers) == 'string')
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
                    if (gettype($attachments) == 'string')
                        $attachments = (array) json_decode('attachments');
                    foreach ($attachments as $attachment) {
                        MessageAttachments::create([
                            'message_id' => $message->id,
                            'file' => $attachment
                        ]);
                    }
                }

                DB::commit();
                $result = Messages::with(['user', 'sender', 'receivers', 'attachments'])->find($message->id);

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

    public function receiver($id)
    {
        try {
            $role = Roles::with(['opd'])->where('id', $id)->first();

            if($role)
            {
                $receivers = [];
                $is_opd = $role->is_opd;

                if($is_opd)
                {
                    $related_roles = RolesOpds::with(['role'])->where('opd_id', $role->id)->get();

                    if($related_roles)
                    {
                        foreach ($related_roles as $related_role) {
                            array_push($receivers, $related_role->role);
                        }
                    }
                    
                }
                else {
                    $receivers = $role->opd;
                }

                $response = [
                    'status' => 200,
                    'message' => 'message receivers has been retrieved',
                    'data' => $receivers
                ];
                return response()->json($response, 200);

            }

            $response = [
                'status' => 404,
                'message' => 'message receivers not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving message receivers data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function outbox(Request $request, $id)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $outbox = Messages::with(['user', 'sender', 'receivers', 'attachments'])->orderBy('messages.' . $sortby, $sorttype)->where('sender_id', $id)
            ->when($keyword, function ($query) use ($keyword) {
                return $query
                    ->where('messages.title', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('messages.content', 'LIKE', '%' . $keyword . '%');
            })->when($row, function($query) use ($row) {
                    return $query
                        ->paginate($row);
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });

            if ($outbox) {
                $response = [
                    'status' => 200,
                    'message' => 'outbox message data has been retrieved',
                    'data' => $outbox
                ];
                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'outbox message not found',
            ];
            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving inbox message data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }


    public function inbox(Request $request, $id)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $inbox = MessageReceivers::with(['message'])->orderBy('message_receivers.' . $sortby, $sorttype)->where('receiver_id', $id)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('message_receivers.message.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('message_receivers.messages.content', 'LIKE', '%' . $keyword . '%');
                })->when($row, function($query) use ($row) {
                    return $query
                        ->paginate($row);
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });

            if ($inbox) {
                foreach ($inbox as $_inbox) {
                    $_inbox->message->sender;
                    $_inbox->message->attachments;
                }

                $response = [
                    'status' => 200,
                    'message' => 'inbox message data has been retrieved',
                    'data' => $inbox
                ];
                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'inbox message not found',
            ];
            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving inbox message data',
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

            if ($message) {
                $message->title = $request->input('title');
                $message->content = $request->input('content');
                if ($request->input('receivers')) {
                    $receivers = $request->input('receivers');
                    if (gettype($receivers) == 'string')
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
