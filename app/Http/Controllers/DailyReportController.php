<?php

namespace App\Http\Controllers;

use App\Exports\DailyReportExport;
use App\Models\DailyReport;
use App\Models\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\NumberFormatter;
use Illuminate\Support\Facades\DB;

// class DailyReportExport implements FromCollection
// {
//     public function collection()
//     {
//         return DailyReport::all();
//     }
// }
// fix excel lib
class DailyReportController extends Controller
{

    public function downloadSummary(Request $request)
    {
        $firstdate = $request->input('firstdate');
        $lastdate = $request->input('lastdate');
        // $dailyReport = DailyReport::with('user')->where('date', '>=', DATE($firstdate))->where('date', '<=', DATE($lastdate))->get();
        // $dailyReport = DailyReport::select(
            // 'created_at',
            // 'name',
            // 'nip',
            // 'position',
            // 'role',
            // 'date',
            // 'report'
        // )->where('date', '>=', DATE($firstdate))->where('date', '<=', DATE($lastdate))->get();
        $dailyReport = DB::table('daily_reports')
        ->select(
            DB::raw('CONVERT(daily_reports.created_at, DATE) as date'),
            DB::raw('CONVERT(daily_reports.created_at, TIME) as time'),
            'daily_reports.name',
            // DB::raw('CONCAT(CONVERT(daily_reports.nip, NCHAR), " " ) as nip'),
            'daily_reports.nip',
            'users.position',
            'users.group',
            'daily_reports.report'
        )->where('date', '>=', DATE($firstdate))->where('date', '<=', DATE($lastdate))
        ->join('users', 'daily_reports.nip', '=', 'users.nip')
        ->get();
        // foreach ($dailyReport as $report) {
        //     $report->date = Carbon::createFromFormat('Y-m-d H:i:s', $report->date)->format('Y.m.d');
        //     $report->time = Carbon::createFromFormat('Y-m-d H:i:s', $report->time)->format('H:i:s');
        // }
        Excel::store(new DailyReportExport($dailyReport), 'daily_report.xlsx');
        return response()->download(storage_path("app/daily_report.xlsx"), "daily_report.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
    }

    public function getByDate(Request $request)
    {

        try {
            $firstdate = $request->input('firstdate');
            $lastdate = $request->input('lastdate');

            $dailyReport = DailyReport::with("user")->where('date', '>=', DATE($firstdate))->where('date', '<=', DATE($lastdate))->get();

            if ($dailyReport) {
                $response = [
                    'status' => 200,
                    'message' => 'daily report has been retrieved',
                    'data' => $dailyReport
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'daily report data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving daily report',
            ];

            return response()->json($response, 400);
        }
    }

    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $role_id = $request->input('role_id');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);


        try {

            $role = Roles::find($role_id);
            $is_opd = 1;
            $role_name = "";
            if ($role) {
                $is_opd = $role->is_opd;
                $role_name = $role->name;
            } else {
                $response = [
                    'status' => 404,
                    'message' => 'role not found, make sure role id is valid',
                ];
                return response()->json($response, 404);
            }


            $dailyReport = DailyReport::with("user")->orderBy('daily_reports.' . $sortby, $sorttype)
                ->when($is_opd && $role->name != 'ADMIN', function ($query) use ($role_name) {
                    return $query
                        ->where('daily_reports.role', $role_name);
                })
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('daily_reports.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('daily_reports.email', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('daily_reports.nip', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('daily_reports.position', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('daily_reports.role', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);


            if ($dailyReport) {
                $response = [
                    'status' => 200,
                    'message' => 'daily report data has been retrieved',
                    'data' => $dailyReport
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving daily report data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }


    public function create(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'name' => 'required',
            'nip' => 'required',
            'position' => 'required',
            'role' => 'required',
            'report' => 'required'
        ]);

        try {
            $dailyReport = DailyReport::create([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'nip' => $request->input('nip'),
                'position' => $request->input('position'),
                'role' => $request->input('role'),
                'date' => Carbon::now()->toDateString(),
                'report' => $request->input('report')
            ]);
            if ($dailyReport) {
                $response = [
                    'status' => 201,
                    'message' => 'daily report data has been created',
                    'data' => $dailyReport
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating daily report data',
                'data' => $e
            ];

            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'report' => 'required'
        ]);

        try {
            $dailyReport = DailyReport::find($id);

            if ($dailyReport) {
                $dailyReport->report = $request->input('report');
                $dailyReport->save();

                $response = [
                    'status' => 200,
                    'message' => 'daily report data has been updated',
                    'data' => $dailyReport
                ];

                return response()->json($response, 200);
            }

            $response = [
                'status' => 404,
                'message' => 'daily report data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on updating daily report data',
                'data' => $e
            ];

            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $dailyReport = DailyReport::findOrFail($id);

            if (!$dailyReport->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'daily report data not found',
                ];

                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'daily report data has been deleted',
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting daily report data',
                'data' => $e
            ];

            return response()->json($response, 400);
        }
    }
}
