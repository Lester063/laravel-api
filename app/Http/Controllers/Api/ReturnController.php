<?php

namespace App\Http\Controllers\api;

use App\Models\Returns;
use App\Models\Requests;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexAdmin()
    {
        //$returns = Returns::all();
        $returns = Returns::join('requests','requests.id','=','returns.idrequest')
        ->join('items','items.id','=','requests.iditem')
        ->join('users', 'users.id','=','returns.idreturner')
        ->select('*','returns.id as id')->get();

        return response()->json([
            'message' => $returns
        ], 200);
    }

    public function indexUser()
    {
        //$returns = Returns::all();
        $returns = Returns::where('idreturner', Auth::id())->join('requests','requests.id','=','returns.idrequest')
        ->join('items','items.id','=','requests.iditem')
        ->join('users', 'users.id','=','returns.idreturner')
        ->select('*','returns.id as id')->get();

        return response()->json([
            'message' => $returns
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $getrequest = Requests::find($request->idrequest);
        // var $hasReturnPending = false;

        $hasPendingReturn = Returns::where('idrequest', $request->idrequest)->where('is_approve', 0)->count();
        // foreach($getrequest)

        if(!$getrequest) {
            return response()->json([
                'message' => 'Unable to find the request.',
            ], 422);
        } 
        else if($getrequest->idrequester != Auth::id()) {
            return response()->json([
                'message' => 'You cannot return this item.',
            ], 422);
        }
        else if($hasPendingReturn >= 1) {
            return response()->json([
                'message' => 'There is already a pending return for this request.',
            ], 422);
        }
        else {
            $return = Returns::create([
                'idrequest' => $request->idrequest,
                'idreturner' => Auth::id(),
                'is_approve' => false,
            ]);

            if($return) {
                $getrequest->update([
                    'isreturnsent' => true
                ]);
            }

            return response()->json([
                'message' => 'Request return was sent successfully.',
                'data' => $return
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function approve(Request $request, string $id)
    {
        $return = Returns::find($id);
        

        //$returnwithjoin = Returns::where('returns.id', $id)->join('requests','requests.id','=','returns.idrequest')->select('*','returns.id as id')->get();
        // return response()->json([
        //     'data' => $returnwithjoin,
        //     'item' => $item,
        //     'iditem' => $item->id,
        // ],200);

        if(!$return) {
            return response()->json([
                'message' => 'Unable to find the return request.',
            ], 422);
        }
        else if($return->is_approve) {
            return response()->json([
                'message' => 'Return request is already approved.',
            ], 200);
        }
        else {
            $return->update([
                'is_approve' => true
            ]);
            
            $returnnewdata = Returns::find($id);
            if($returnnewdata->is_approve) {
                $requests = Requests::find($return->idrequest);
                $item = Item::find($requests->iditem);

                $item->update([
                    'is_available' => true
                ]);
            }

            return response()->json([
                'message' => 'Request for return has been approved.',
                'data' => $returnnewdata
            ], 200);
        }
    }
}