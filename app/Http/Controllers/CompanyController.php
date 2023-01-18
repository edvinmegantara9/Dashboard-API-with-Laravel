<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyClient;
use App\Models\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
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
            $data = Company::with('company_services', 'company_clients')->orderBy('companies.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('companies.name', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'companies data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving categorie data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
            'address'      => 'required',
            'email'        => 'required',
            'phone_number' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $company = new Company;
            $company->name          = $request->input('name');
            $company->address       = $request->input('address');
            $company->email         = $request->input('email');
            $company->phone_number  = $request->input('phone_number');
            $company->logo          = $request->input('logo');
            $company->about         = $request->input('about');
            $company->vision        = $request->input('vision');
            $company->mision        = $request->input('mision');
            $company->test_instruction    = $request->input('test_instruction');
            $company->payment_instruction = $request->input('payment_instruction');
            $company->about_application   = $request->input('about_application');
            $company->privacy_policy      = $request->input('privacy_policy');
            $company->developer_policy    = $request->input('developer_policy');
            $company->term_and_condition  = $request->input('term_and_condition');
    
            if ($company->save()) {

                if (!empty($request->get('company_services'))) {
                    foreach ($request->get('company_services') as $d) {
                        $detail = new CompanyService();
                        $detail->company_id     = $company->id;
                        $detail->title          = $d['title'];
                        $detail->description    = $d['description'];
                        $detail->save();
                    }
                }
				
                if (!empty($request->get('company_clients'))) { 
                    foreach ($request->get('company_clients') as $d) {
                        $detail = new CompanyClient();
                        $detail->company_id     = $company->id;
                        $detail->name           = $d['name'];
                        $detail->logo           = $d['logo'];
                        $detail->save();
                    }
                }
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'company data has been created',
                'data' => $company
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating company data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required',
            'address'      => 'required',
            'email'        => 'required',
            'phone_number' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $company = Company::find($id);

            if ($company) {
                $company->name          = $request->input('name');
                $company->address       = $request->input('address');
                $company->email         = $request->input('email');
                $company->phone_number  = $request->input('phone_number');
                $company->logo          = $request->input('logo');
                $company->about         = $request->input('about');
                $company->vision        = $request->input('vision');
                $company->mision        = $request->input('mision');
                $company->test_instruction    = $request->input('test_instruction');
                $company->payment_instruction = $request->input('payment_instruction');
                $company->about_application   = $request->input('about_application');
                $company->privacy_policy      = $request->input('privacy_policy');
                $company->developer_policy    = $request->input('developer_policy');
                $company->term_and_condition  = $request->input('term_and_condition');

                if ($company->save()) {
                    CompanyService::where('company_id', $company->id)->delete();
                    if (!empty($request->get('company_services'))) {
                        foreach ($request->get('company_services') as $d) {
                            $detail = new CompanyService();
                            $detail->company_id     = $company->id;
                            $detail->title          = $d['title'];
                            $detail->description    = $d['description'];
                            $detail->save();
                        }
                    }
                    
                    CompanyClient::where('company_id', $company->id)->delete();
                    if (!empty($request->get('company_clients'))) { 
                        foreach ($request->get('company_clients') as $d) {
                            $detail = new CompanyClient();
                            $detail->company_id     = $company->id;
                            $detail->name           = $d['name'];
                            $detail->logo           = $d['logo'];
                            $detail->save();
                        }
                    }
                }

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'company data has been updated',
                    'data' => $company
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'company data not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating company data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $company = Company::findOrFail($id);
            
            if (!$company->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'company data not found',
                ];
                return response()->json($response, 404);
            }

            $response = [
                'status' => 200,
                'message' => 'company data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating company data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function show($id) {
        $company = Company::with('company_services', 'company_clients')->where('id', $id)->first();
    
        if (!$company) {
            $response = [
                'status' => 404,
                'message' => 'Company tidak ditemukan!',
            ];
            return response()->json($response, 404);
        }

        $response = [
            'status' => 200,
            'data' => $company,
        ];
        return response()->json($response, 200);
    }
}
