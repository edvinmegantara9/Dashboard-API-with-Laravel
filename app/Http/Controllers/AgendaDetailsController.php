<?php

namespace App\Http\Controllers;

use App\Models\AgendaDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaDetailsController extends Controller
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

            $agendaDetails = AgendaDetails::with(['agenda'])->orderBy('agenda_details.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('agenda_details.agenda_type', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('agenda_details.schedule', 'LIKE', '%' . $keyword . '%');
                })->when($row, function($query) use ($row) {
                    return $query
                        ->paginate($row);
                })
                ->when(!$row, function ($query) use ($row) {
                    return $query
                        ->get();
                });


            if ($agendaDetails) {
                $response = [
                    'status' => 200,
                    'message' => 'agenda detail data has been retrieved',
                    'data' => $agendaDetails
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving agenda detail data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'agenda_id' => 'required',
            'agenda_type' => 'required|string',
            'schedule' => 'required|string'
        ]);

        try {
            $agendaDetails = AgendaDetails::create(
                [
                    'agenda_id' => $request->input('agenda_id'),
                    'agenda_type' => $request->input('agenda_type'),
                    'schedule' => $request->input('schedule')
                ]
            );

            if ($agendaDetails) {
                $response = [
                    'status' => 201,
                    'message' => 'agenda detail data has been created',
                    'data' => $agendaDetails
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating agenda detail data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'agenda_id' => 'required',
            'agenda_type' => 'required|string',
            'schedule' => 'required|string'
        ]);

        try {
            $agendaDetails = AgendaDetails::find($id);

            if($agendaDetails)
            {
                $agendaDetails->agenda_id = $request->input('agenda_id');
                $agendaDetails->agenda_type = $request->input('agenda_type');
                $agendaDetails->schedule = $request->input('schedule');
                $agendaDetails->save();

                $response = [
                    'status' => 200,
                    'message' => 'agenda detail data has been updated',
                    'data' => $agendaDetails
                ];

                return response()->json($response, 200);
            }
            
            $response = [
                'status' => 404,
                'message' => 'agenda detail not found',
            ];

            return response()->json($response, 404);


        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating agenda detail data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $agendaDetails = AgendaDetails::findOrDetail($id);

            if(!$agendaDetails->delete())
            {
                $response = [
                    'status' => 404,
                    'message' => 'agenda detail not found',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'agenda detail data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting agenda detail data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
