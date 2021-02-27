<?php

namespace App\Http\Controllers;

use App\Models\ItemActions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getReport1Result(Request $request)
    {
        dd($request);
        $paramsKeys = ['start_date', 'end_date', 'status'];
    }

    public function getReport2Result(Request $request)
    {
        $where = '1 ';

        if ($request->start_date && !isset($request->end_date))
            $where .= ' and start_time > "' . $request->start_date . '"';

        if ($request->end_date && !isset($request->start_date))
            $where .= ' and start_time < "' . $request->end_date . '"';

        if ($request->end_date && $request->start_date)
            $where .= ' and start_time between "' . $request->start_date . '" and "' . $request->end_date . '" ';


        $file_name = 'report2.csv';

        // the equivalent sql query
        /*
         * select DATE(item_actions.start_time) as Date, category.name as category, todo.description, item_actions.time_spent,
  sum(item_actions.time_spent)	as 'amount of time spent' from item_actions
  left join todo on item_actions.item_id = todo.id
  left join category on category.id = todo.category_id

GROUP BY DATE(item_actions.start_time), todo.id;
         */
        $report_items = ItemActions::select(DB::raw('DATE(item_actions.start_time) as Date'),
            'category.name as Category', 'todo.description as Description',
            DB::raw('sum(item_actions.time_spent)	as "spent_time" '))
            ->leftJoin('todo', 'item_actions.item_id', 'todo.id')
            ->leftJoin('category', 'category.id', 'todo.category_id')
            ->whereRaw($where)
            ->groupBy(['Date', 'todo.id'])->get();

        if (!count($report_items)) {
            return response()->json('No actions during that date range', Response::HTTP_OK);
        }
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$file_name",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('Date', 'Category', 'Description', 'amount of time spent');

        $stream = function () use ($report_items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($report_items as $task) {
                $row['Date'] = $task->Date;
                $row['Category'] = $task->Category;
                $row['Description'] = $task->Description;
                $row['amount of time spent'] = UtilitiesController::secondsToString($task->spent_time);

                fputcsv($file, array($row['Date'], $row['Category'], $row['Description'], $row['amount of time spent']));
            }

            fclose($file);
        };
        return response()->stream($stream, 200, $headers);
    }
}
