<?php

namespace App\Http\Controllers;

use App\Lookups\StatusLookup;
use App\Models\TODO;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TODOController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todo_list = TODO::all();
        return response()->json($todo_list, Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|min:2|max:256',
        ]);

        if ($validator->fails()) {
            // TODO return the errors in array
            return response()->json($validator->errors()->get('description')[0], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        try {
            $request->merge(['status'=> StatusLookup::WAITING]);
            TODO::create($request->all());
            return response()->json('TODO item created successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            Log::info('[CREATE_TODO] Error: ' . $e->getMessage());
            return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TODO  $tODO
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TODO $tODO)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TODO  $tODO
     * @return \Illuminate\Http\Response
     */
    public function destroy(TODO $tODO)
    {
        //
    }
}
