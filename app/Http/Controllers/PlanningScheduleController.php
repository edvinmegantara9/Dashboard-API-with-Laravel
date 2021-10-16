<?php

namespace App\Http\Controllers;

use App\Models\PlanningSchedule;
use Illuminate\Http\Request;

class PlanningScheduleController extends Controller
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
            $planning_schedules = PlanningSchedule::orderBy('planning_schedules.'. $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('planning_schedules.plan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('planning_schedules.schedule', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);

            if ($planning_schedules) {
                $response = [
                    'status' => 200,
                    'message' => 'schedule plan data has been retrieved',
                    'data' => $planning_schedules
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving schedule plan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'plan' => 'required',
            'schedule' => 'required',
            'type' => 'required'
        ]);

        try {

            $planning_schedules = PlanningSchedule::create([
                'plan' => $request->input('plan'),
                'schedule' => $request->input('schedule'),
                'type' => $request->input('type') 
            ]);

            if ($planning_schedules) {
                $response = [
                    'status' => 201,
                    'message' => 'schedule plan data has been created',
                    'data' => $planning_schedules
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating schedule plan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'plan' => 'required',
            'schedule' => 'required',
            'type' => 'required'
        ]);

        try {

            $planning_schedules = PlanningSchedule::find($id);

            if ($planning_schedules) {

                $planning_schedules->plan = $request->input('plan');
                $planning_schedules->schedule = $request->input('schedule');
                $planning_schedules->type = $request->input('type');
                $planning_schedules->save();

                $response = [
                    'status' => 200,
                    'message' => 'schedule plan data has been updated',
                    'data' => $planning_schedules
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'schedule plan data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating schedule plan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $planning_schedules = PlanningSchedule::findOrFail($id);

            if (!$planning_schedules->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'schedule plan data not found',
                ];

                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'schedule plan data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting schedule plan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
