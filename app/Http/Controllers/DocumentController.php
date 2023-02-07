<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\DocumentNotice;
use App\Models\DocumentStatus;
use App\Models\DocumentConsider;
use App\Models\DocumentRemember;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        

        $this->validate($request,[
            'document_type' => 'required',
            'tittle' => 'required',
            'signer' => 'required',
            'date' => 'required',
            // 
            'document_considers' => 'required|array|min:1',
            'document_considers.*.description' => 'required',
            'document_considers.*.margin_top' => 'required|integer',
            'document_considers.*.margin_bottom' => 'required|integer',
            'document_considers.*.margin_left' => 'required|integer',
            'document_considers.*.margin_right' => 'required|integer',
            // 
            'document_remembers' => 'required|array|min:1',
            'document_remembers.*.description' => 'required',
            'document_remembers.*.margin_top' => 'required|integer',
            'document_remembers.*.margin_bottom' => 'required|integer',
            'document_remembers.*.margin_left' => 'required|integer',
            'document_remembers.*.margin_right' => 'required|integer',
            // 
            'document_notices' => 'required|array|min:1',
            'document_notices.*.description' => 'required',
            'document_notices.*.margin_top' => 'required|integer',
            'document_notices.*.margin_bottom' => 'required|integer',
            'document_notices.*.margin_left' => 'required|integer',
            'document_notices.*.margin_right' => 'required|integer',
            // 
            'document_statuses.status' => 'required',
            'document_statuses.remark' => 'required',

            



        ]);

    try{
        DB::beginTransaction();
        $doc = new Document;
        $doc->document_type = $request->input('document_type');
        $doc->tittle = $request->input('tittle');
        $doc->signer = $request->input('signer');
        $doc->user_id = Auth::user()->id;
        $doc->date = $request->input('date');
        $doc->save();

        if (count($request->get('document_considers')) > 0) {
            foreach ($request->get('document_considers') as $d) {
                $consider = new DocumentConsider;
                $consider->document_id = $doc->id;
                $consider->description = $d['description'];
                $consider->margin_top =$d['margin_top'];
                $consider->margin_bottom =$d['margin_bottom'];
                $consider->margin_left =$d['margin_left'];
                $consider->margin_right =$d['margin_right'];
                $consider->save();
            }
        }

        if (count($request->get('document_remembers')) > 0) {
            foreach ($request->get('document_remembers') as $d) {
                $remembers = new DocumentRemember;
                $remembers->document_id = $doc->id;
                $remembers->description = $d['description'];
                $remembers->margin_top =$d['margin_top'];
                $remembers->margin_bottom =$d['margin_bottom'];
                $remembers->margin_left =$d['margin_left'];
                $remembers->margin_right =$d['margin_right'];
                $remembers->save();
            }
        }
        if (count($request->get('document_notices')) > 0) {
            foreach ($request->get('document_notices') as $d) {
                $notices = new DocumentNotice;
                $notices->document_id = $doc->id;
                $notices->description = $d['description'];
                $notices->margin_top =$d['margin_top'];
                $notices->margin_bottom =$d['margin_bottom'];
                $notices->margin_left =$d['margin_left'];
                $notices->margin_right =$d['margin_right'];
                $notices->save();
            }
        }
        $status = new DocumentStatus;
        $status->document_id = $doc->id;
        $status->status = $request->input('document_statuses.status');
        $status->remark = $request->input('document_statuses.remark');
        $status->user_id = Auth::user()->id;
        $status->save();

        DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil!',
                
            ];

            return response()->json($response, 201);

    }catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat menambahkan data Dokumen',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);

    };


    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        //
    }
}
