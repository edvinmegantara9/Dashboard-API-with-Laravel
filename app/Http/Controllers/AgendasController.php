<?php

namespace App\Http\Controllers;

use App\Models\AgendaDetails;
use App\Models\Agendas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendasController extends Controller
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

            $agendas = Agendas::with(['schedules'])->orderBy('agendas.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('agendas.title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('agendas.content', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);


            if ($agendas) {
                $response = [
                    'status' => 200,
                    'message' => 'agenda data has been retrieved',
                    'data' => $agendas
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving agenda data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'agenda_detail' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $agendas = Agendas::create([
                'title' => $request->input('title'),
                'content' => $request->input('content')
            ]);

            if ($agendas) {
                $agenda_details = $request->input('agenda_detail');
                if(gettype($agenda_details) == 'string')
                $agenda_details = (array) json_decode($agenda_details);
                foreach ($agenda_details as $agenda) {
                    AgendaDetails::create([
                        'agenda_id' => $agendas->id,
                        'agenda_type' => $agenda->agenda_type,
                        'schedule' => $agenda->schedule
                    ]);
                }
                DB::commit();
                $agendas->schedules;
                $response = [
                    'status' => 201,
                    'message' => 'agenda data has been created',
                    'data' => $agendas
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating agenda data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {


        $this->validate($request, [
            'title' => 'required',
            'content' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $agendas = Agendas::find($id);

            if ($agendas) {
                $agendas->title = $request->input('title');
                $agendas->content = $request->input('content');
                if ($request->input('agenda_detail')) {
                    AgendaDetails::where('agenda_id', $id)->delete();
                    $agenda_details = $request->input('agenda_detail');
                    if(gettype($agenda_details) == 'string')
                    $agenda_details = (array) json_decode($agenda_details);
                    foreach ($agenda_details as $agenda) {
                        AgendaDetails::create([
                            'agenda_id' => $agendas->id,
                            'agenda_type' => $agenda->agenda_type,
                            'schedule' => $agenda->schedule
                        ]);
                    }
                }

                $agendas->save();
                DB::commit();
                $agendas->schedules;

                $response = [
                    'status' => 200,
                    'message' => 'agenda data has been updated',
                    'data' => $agendas
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'agenda data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating agenda data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {

        try {
            DB::beginTransaction();
            $agendas = Agendas::findOrFail($id);

            if ($agendas)
                AgendaDetails::where('agenda_id', $id)->delete();

            if (!$agendas->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'agenda data not found',
                ];
                return response()->json($response, 404);
            }

            DB::commit();
            $response = [
                'status' => 200,
                'message' => 'agenda data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting agenda data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
