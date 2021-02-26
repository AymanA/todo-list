<?php

namespace App\Http\Controllers;

use App\Lookups\StatusLookup;
use App\Models\ItemActions;
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
     * @param \Illuminate\Http\Request $request
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
            $request->merge(['status' => StatusLookup::WAITING]);
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TODO $tODO
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TODO $tODO)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\TODO $tODO
     * @return \Illuminate\Http\Response
     */
    public function destroy(TODO $tODO)
    {
        //
    }

    public function trackItem(Request $request, $item_id)
    {
        $exist = $this->checkItemExist($item_id);

        if (!$exist)
            return response()->json('item not found', Response::HTTP_NOT_FOUND);

        $item_is_active = ItemActions::where('item_id', $item_id)->where('tracking', 1)->first();

        if ($item_is_active) {
            return response()->json(' item under tracking', Response::HTTP_BAD_REQUEST);
        } else {
            $current_tracked_item = ItemActions::where('tracking', 1)->first();
            if ($current_tracked_item) {
                return response()->json('there is an item under tracking', Response::HTTP_BAD_REQUEST);
            }
        }

        ItemActions::create([
            'item_id' => $item_id
        ]);
        return response()->json('Item tracking started successfully', Response::HTTP_OK);
    }

    public function stopItem(Request $request, $item_id)
    {
        $itemExist = TODO::where('id', $item_id)->first();

        if (!$itemExist)
            return response()->json('item not found', Response::HTTP_NOT_FOUND);

        $item_is_active = ItemActions::where('item_id', $item_id)->where('tracking', 1)->first();

        if ($item_is_active) {
            try{
                $item_is_active->tracking = 0;
                $item_is_active->save();

                $spent_time = $this->calculateSpentTimeForItem($item_is_active->id);

                $itemExist->time_spent += $spent_time;
                $itemExist->save();

                return response()->json('Item tracking stopped successfully', Response::HTTP_OK);
            } catch (Exception $e){
                Log::info('[STOP_TRACK_ERROR] Error: ' . $e->getMessage());
                Log::info('[STOP_TRACK_ERROR] item_id ' . $item_id);
                return response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return response()->json('item already stopped', Response::HTTP_BAD_REQUEST);
        }


    }

    public function checkItemExist($item_id)
    {
        $itemExist = TODO::where('id', $item_id)->first();
        if (!$itemExist) {
            return false;
        }

        return true;
    }

    public function calculateSpentTimeForItem($action_item_id){
        $updated_action = ItemActions::where('id', $action_item_id)->first();
        $start_time = strtotime($updated_action->start_time);
        $stop_time = strtotime($updated_action->stop_time);

        $seconds_diff = $stop_time - $start_time;

        return $seconds_diff;
    }
}
