<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DocumentNotice;
use App\Models\DocumentStatus;
use App\Models\DocumentConsider;
use App\Models\DocumentRemember;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DocumentAttachment;
use App\Models\DocumentSupport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class DocumentController extends Controller
{
    public function get(Request $request)
    {
        $row        = $request->input('row');
        $keyword    = $request->input('keyword');
        $sortby     = $request->input('sortby');
        $sorttype   = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = Document::orderBy('documents.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('documents.document_type', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('documents.document_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('documents.tittle', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('documents.signer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('documents.status', 'LIKE', '%' . $keyword . '%');
                        
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'menus data has been retrieved',
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
        $this->validate($request,[
            'document_type' => 'required',
            'tittle' => 'required',
            'signer' => 'required',
            'date' => 'required',
            'status' => 'required',
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
            // kalau notices ga selalu ada, required nya tinggal hapus
            'document_notices' => 'required|array|min:1',
            'document_notices.*.description' => 'required',
            'document_notices.*.margin_top' => 'required|integer',
            'document_notices.*.margin_bottom' => 'required|integer',
            'document_notices.*.margin_left' => 'required|integer',
            'document_notices.*.margin_right' => 'required|integer',
            
            // 'document_statuses.status' => 'required',
            // 'document_statuses.remark' => 'required',
            
            'document_attachments' => 'required|array|min:1',
            'document_attachments.*.description' => 'required',
            'document_attachments.*.margin_top' => 'required|integer',
            'document_attachments.*.margin_bottom' => 'required|integer',
            'document_attachments.*.margin_left' => 'required|integer',
            'document_attachments.*.margin_right' => 'required|integer',
            
            'document_supports.*.file' => 'required',
        ]);

        try{
            DB::beginTransaction();
            $doc = new Document;
            $doc->document_type = $request->input('document_type');
            $doc->tittle = $request->input('tittle');
            $doc->signer = $request->input('signer');
            $doc->user_id = Auth::user()->id;
            $doc->date = $request->input('date');
            $doc->status = $request->input('status');
            $doc->legal_drafter = $request->input('legal_drafter');
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

            if (count($request->get('document_attachments')) > 0) {
                foreach ($request->get('document_attachments') as $d) {
                    $attachment = new DocumentAttachment();
                    $attachment->document_id = $doc->id;
                    $attachment->tittle = $d['tittle'];
                    $attachment->description = $d['description'];
                    $attachment->margin_top =$d['margin_top'];
                    $attachment->margin_bottom =$d['margin_bottom'];
                    $attachment->margin_left =$d['margin_left'];
                    $attachment->margin_right =$d['margin_right'];
                    $attachment->save();
                            
                }
            }

            if (count($request->get('document_supports')) > 0) {
                foreach ($request->get('document_supports') as $d) {
                    $suport = new DocumentSupport();
                    $suport->document_id = $doc->id;
                    $suport->file = $d['file'];    
                    $suport->save();            
                }
            }
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

    public function delete($id){
        try {
            $doc = Document::find($id);
            
            if (!$doc) {
                $response = [
                    'status' => 404,
                    'message' => 'Data tidak ditemukan!',
                ];
                return response()->json($response, 404);
            }

           
            DocumentConsider::where('document_id', $id)->delete();
            #harus direfactor nanti
            if(DocumentNotice::where('document_id', $id)){
                DocumentNotice::where('document_id', $id)->delete();
            };
            DocumentRemember::where('document_id', $id)->delete();
            DocumentStatus::where('document_id', $id)->delete();
            DocumentAttachment::where('document_id', $id)->delete();
            DocumentSupport::where('document_id',$id)->delete();
            $doc->delete();


            $response = [
                'status' => 200,
                'message' => 'Data Dokumen berhasil dihapus!',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat menghapus Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function show($id)
    {
        try {           
            $data = Document::with([
                'document_considers',
                'document_remembers',
                'document_notices',
                'document_statuses',
                'document_decisions',
                'document_attachments',
                'document_supports'])
                ->where('id', $id)
                ->firstOrFail();
            
            if($data!=null){
                $response = [
                    'status' => 200,
                    'message' => 'Data Dokumen ditemukan',
                    'data' => $data, 
                ];
                return response()->json($response,200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat Show Data Document',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'document_type' => 'required',
            'tittle' => 'required',
            'signer' => 'required',
            'date' => 'required',
            'status' => 'required',
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
            //kalau notices ga selalu ada, required nya tinggal hapus
            'document_notices' => 'required|array|min:1',
            'document_notices.*.description' => 'required',
            'document_notices.*.margin_top' => 'required|integer',
            'document_notices.*.margin_bottom' => 'required|integer',
            'document_notices.*.margin_left' => 'required|integer',
            'document_notices.*.margin_right' => 'required|integer',

            'document_attachments' => 'required|array|min:1',
            'document_attachments.*.description' => 'required',
            'document_attachments.*.margin_top' => 'required|integer',
            'document_attachments.*.margin_bottom' => 'required|integer',
            'document_attachments.*.margin_left' => 'required|integer',
            'document_attachments.*.margin_right' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $doc = Document::find($id);

            if ($doc) {
                $doc->document_type = $request->input('document_type');
                $doc->tittle = $request->input('tittle');
                $doc->signer = $request->input('signer');
                $doc->date = $request->input('date');
                $doc->status = $request->input('status');
                $doc->save();

                if (!empty($request->get('document_considers'))) {
                    DocumentConsider::where('document_id', $doc->id)->delete();
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

                if (!empty($request->get('document_remembers'))) {
                    DocumentRemember::where('document_id', $doc->id)->delete();
                    foreach ($request->get('document_remembers') as $d) {
                        $remember = new DocumentRemember();
                        $remember->document_id = $doc->id;
                        $remember->description = $d['description'];
                        $remember->margin_top =$d['margin_top'];
                        $remember->margin_bottom =$d['margin_bottom'];
                        $remember->margin_left =$d['margin_left'];
                        $remember->margin_right =$d['margin_right'];
                        $remember->save();                        
                    }
                }

                if (!empty($request->get('document_notices'))) {
                    DocumentNotice::where('document_id', $doc->id)->delete();
                    foreach ($request->get('document_notices') as $d) {
                        $notice = new DocumentNotice;
                        $notice->document_id = $doc->id;
                        $notice->description = $d['description'];
                        $notice->margin_top =$d['margin_top'];
                        $notice->margin_bottom =$d['margin_bottom'];
                        $notice->margin_left =$d['margin_left'];
                        $notice->margin_right =$d['margin_right'];
                        $notice->save();                        
                    }
                }

                if (!empty($request->get('document_attachments'))) {
                    DocumentAttachment::where('document_id', $doc->id)->delete();
                    foreach ($request->get('document_attachments') as $d) {
                        $attachment = new DocumentAttachment();
                        $attachment->document_id = $doc->id;
                        $attachment->tittle = $d['tittle'];
                        $attachment->description = $d['description'];
                        $attachment->margin_top =$d['margin_top'];
                        $attachment->margin_bottom =$d['margin_bottom'];
                        $attachment->margin_left =$d['margin_left'];
                        $attachment->margin_right =$d['margin_right'];
                        $attachment->save();                        
                    }
                }

                if (!empty($request->get('document_supports'))) {
                    DocumentSupport::where('document_id', $doc->id)->delete();
                    foreach ($request->get('document_supports') as $d) {
                        $suport = new DocumentSupport();
                        $suport->document_id = $doc->id;
                        $suport->file = $d['file'];    
                        $suport->save();
                    }
                }

                if (!empty($request->get('document_statuses'))) {
                    DocumentStatus::where('document_id', $doc->id)->delete();
                    $status = new DocumentStatus;
                    $status->document_id = $doc->id;
                    $status->status = $request->input('document_statuses.status');
                    $status->remark = $request->input('document_statuses.remark');
                    $status->user_id = Auth::user()->id;
                    $status->save();
                }

                DB::commit();
                $response = [
                    'status' => 200,
                    'message' => 'Data Document Behasil di update',
                    
                ];
                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'Data tidak ditemukan!',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error pada saat mengubah data!',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

   public function approveAdmin(Request $request) {
        try {
            DB::beginTransaction();
            
            $doc = Document::find($request->input('document_id'));
            $doc->status = $request->input('status');

            $status = new DocumentStatus;
            $status->user_id =  Auth::user()->id;
            $status->document_id = $request->input('document_id');
            $status->status = $request->input('status');
            $status->remark = $request->input('remark');
            $status->save();    
            
            if($request->input('status')=='PROSES'){
                $documents = Document::select(DB::raw('COUNT(*) as total_document, legal_drafter'))
                        ->groupBy('legal_drafter')->get();

                return $documents;
                        
                if (count($documents) == 1) {
                    // case belum ada sama sekali
                    if (empty($documents[0]->legal_drafter)) {
                        // ambil user legal drafter paling pertama 
                        $legal_drafter = User::where('verificator', 2)->first();
                    } else {
                        // kalo ada ambil first except yang sudah ada
                        $legal_drafter = User::where('verificator', 2)->where('id', '!=', $documents[0]->legal_drafter)->first();
                    }
                } else {
                    $legal_drafters = [];
                    foreach ($documents as $key => $document) {
                        if (!empty($document->legal_drafter)) {
                            array_push($legal_drafters, $document->legal_drafter);
                        }
                    }
                    $legal_drafter = User::where('verificator', 2)
                        ->whereNotIn('id', $legal_drafters)
                        ->first();
                    if (!$legal_drafter) {
                        $documents = $documents->toArray();
                        $object = array_reduce($documents, function($a, $b){
                            return ($a['total_document'] < $b['total_document']) && (!empty($a['legal_drafter']))  ? $a : $b;
                        }, array_shift($documents));
                        $legal_drafter = User::where('verificator', 2)->where('id', $object['legal_drafter'])->first();                 
                    }
                }

                $doc->legal_drafter = $legal_drafter->id;
                $doc->admin_verified = Auth::user()->id;
                $doc->admin_verified_at = Carbon::now();
            }

            $doc->save();
            $status->save(); 
            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil di Approve Admin!',
                
            ];

            return response()->json($response, 201);
        } catch(\Exception $e){
            DB::rollBack();
                \Sentry\captureException($e);
                $response = [
                    'status' => 400,
                    'message' => 'Ada error saat update Data',
                    'error' => $e->getMessage()
                ];
                return response()->json($response, 400);

        };
   }

    public function approveLegalDrafter(Request $request){
        try{
            DB::beginTransaction();
            $doc = Document::find($request->input('document_id')); 
            if (!$doc){
                throw new \Exception('Data Dokumen tidak ada');
            }
            $doc->status = $request->input('status');
            $status = new DocumentStatus;
            $status->user_id =  Auth::user()->id;
            $status->document_id = $request->input('document_id');
            $status->status = $request->input('status');
            $status->remark = $request->input('remark');

            if($request->input('status') == 'LEGAL DRAFTING'){
                $doc->legal_drafter_verified = Auth::user()->id;
                $doc->legal_drafter_verified_at = Carbon::now();
            }
            $doc->save();
            $status->save();

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil di Update Legal Drafter!',
                
            ];
            return response()->json($response, 201);
        } catch(\Exception $e){
            DB::rollBack();
                \Sentry\captureException($e);
                $response = [
                    'status' => 400,
                    'message' => 'Ada error saat update Data',
                    'error' => $e->getMessage()
                ];
                return response()->json($response, 400);
        };
   }

   public function approveSuncang(Request $request){
    try{
        DB::beginTransaction();
        $doc = Document::find($request->input('document_id'));
        if(!$doc){
            throw new \Exception('Data Dokumen tidak ada');
        }
        $doc->status = $request->input('status');
        $status = new DocumentStatus;
        $status->user_id =  Auth::user()->id;
        $status->document_id = $request->input('document_id');
        $status->status = $request->input('status');
        $status->remark = $request->input('remark');
        if($request->input('status')=='APPROVED SUNCANG'){
            $doc->suncang_verified = Auth::user()->id;
            $doc->suncang_verified_at = Carbon::now();
        }
        $doc->save();
        $status->save();
        DB::commit();

        $response = [
            'status' => 201,
            'message' => 'Dokumen Berhasil di Update Suncang!',
            
        ];
        return response()->json($response, 201);
    } catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error saat update Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);

    };
   }

   public function approveKasubag(Request $request){
    try{
        DB::beginTransaction();
        $doc = Document::find($request->input('document_id'));
        
        
        if(!$doc){

            throw new \Exception('Data Dokumen tidak ada');

        }
        $doc->status = $request->input('status');
        $status = new DocumentStatus;
        $status->user_id =  Auth::user()->id;
        $status->document_id = $request->input('document_id');
        $status->status = $request->input('status');
        $status->remark = $request->input('remark');
        if($request->input('status')=='APPROVED KASUBAG'){
            
            $doc->kasubag_verified = Auth::user()->id;
            $doc->kasubag_verified_at = Carbon::now();
            $doc->kasubag_verfied_sign = $request->input('sign');
        }
        $doc->save();
        $status->save();
        DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil di Update Kasubag!',
                
            ];

            return response()->json($response, 201);
        

    }catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error saat update Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);

    };
   }

   public function approveKabag(Request $request){
    try{
        DB::beginTransaction();
        $doc = Document::find($request->input('document_id'));
        if(!$doc){
            throw new \Exception('Data Dokumen tidak ada');
        }
        $doc->status = $request->input('status');
        $status = new DocumentStatus;
        $status->user_id =  Auth::user()->id;
        $status->document_id = $request->input('document_id');
        $status->status = $request->input('status');
        $status->remark = $request->input('remark');
        if($request->input('status')=='APPROVED KABAG'){
            $doc->kabag_verified = Auth::user()->id;
            $doc->kabag_verified_at = Carbon::now();
            $doc->kabag_verified_sign = $request->input('sign');
        }
        $doc->save();
        $status->save();
        DB::commit();

        $response = [
            'status' => 201,
            'message' => 'Dokumen Berhasil di Update Kabag!',
        ];

        return response()->json($response, 201);
    }catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error saat update Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);

    };
   }

   public function approveAssistant(Request $request){
    try{
        DB::beginTransaction();
        $doc = Document::find($request->input('document_id'));
        
        
        if(!$doc){

            throw new \Exception('Data Dokumen tidak ada');

        }
        $doc->status = $request->input('status');
        $status = new DocumentStatus;
        $status->user_id =  Auth::user()->id;
        $status->document_id = $request->input('document_id');
        $status->status = $request->input('status');
        $status->remark = $request->input('remark');
        if($request->input('status')=='APPROVED ASSISTANT'){
            
            $doc->asistant_verified = Auth::user()->id;
            $doc->asistant_verified_at = Carbon::now();
            $doc->asistant_verified_sign = $request->input('sign');
        }
        $doc->save();
        $status->save();
        DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil di Update Assistant!',
                
            ];

            return response()->json($response, 201);
        

    }catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error saat update Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);

    };
   }

   public function approveSekda(Request $request){
    try{
        DB::beginTransaction();
        $doc = Document::find($request->input('document_id'));
        
        
        if(!$doc){

            throw new \Exception('Data Dokumen tidak ada');

        }
        $doc->status = $request->input('status');
        $status = new DocumentStatus;
        $status->user_id =  Auth::user()->id;
        $status->document_id = $request->input('document_id');
        $status->status = $request->input('status');
        $status->remark = $request->input('remark');
        if($request->input('status')=='APPROVED SEKDA'){
            
            $doc->sekda_verified = Auth::user()->id;
            $doc->sekda_verified_at = Carbon::now();
            $doc->sekda_verified_sign = $request->input('sign');
        }
        $doc->save();
        $status->save();
        DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Dokumen Berhasil di Update Sekda!',
                
            ];

            return response()->json($response, 201);
        

    }catch(\Exception $e){
        DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Ada error saat update Data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
    };
   }
}
