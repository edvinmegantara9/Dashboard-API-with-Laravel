<?php

namespace App\Http\Controllers;

use App\Models\CitizenReport;
use Illuminate\Http\Request;

class CitizenReportController extends Controller
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
            $citizen_reports = CitizenReport::orderBy('citizen_reports.'. $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('citizen_reports.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('citizen_reports.address', 'LIKE', '%' . $keyword . '%');
                })->paginate($row);

            if ($citizen_reports) {
                $response = [
                    'status' => 200,
                    'message' => 'citizen report data has been retrieved',
                    'data' => $citizen_reports
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving citizen report data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'report' => 'required'
        ]);

        try {

            $citizen_reports = CitizenReport::create([
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
                'report' => $request->input('report')
            ]);

            if ($citizen_reports) {
                $response = [
                    'status' => 201,
                    'message' => 'citizen report data has been created',
                    'data' => $citizen_reports
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating citizen report data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'report' => 'required'
        ]);
        try {

            $citizen_reports = CitizenReport::find($id);

            if ($citizen_reports) {

                $citizen_reports->name = $request->input('name');
                $citizen_reports->address = $request->input('address');
                $citizen_reports->phone_number = $request->input('phone_number');
                $citizen_reports->report = $request->input('report');
                $citizen_reports->save();

                $response = [
                    'status' => 200,
                    'message' => 'citizen report data has been updated',
                    'data' => $citizen_reports
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'citizen report data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating citizen report data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $citizen_reports = CitizenReport::findOrFail($id);

            if (!$citizen_reports->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'citizen report data not found',
                ];

                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'citizen report data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting citizen report data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
