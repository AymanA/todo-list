<?php

namespace App\Http\Controllers;

use App\Models\ItemActions;
use App\Models\TODO;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getReport1Result(Request $request)
    {
        $where = $this->prepareFilter($request);

        $group_by = ['category'];
        $date_field = 'YEAR(item_actions.start_time)';
        switch($request->group_by){
            case 'year':
                $date_field = 'YEAR(item_actions.start_time)';
                $group_by[] = 'YEAR(item_actions.start_time)';
                break;

            case 'month':
                $date_field = 'MONTHNAME(item_actions.start_time)';
                $group_by[] = 'MONTHNAME(item_actions.start_time)';
                break;

            default:
                $date_field = 'DATE(item_actions.start_time)';
                $group_by[] = 'DATE(item_actions.start_time)';
                break;

        }


        $file_name = 'report1.csv';

        $report_items = TODO::select(DB::raw($date_field.' as Date'),
            'category.name as Category',
        DB::raw('count( distinct(case when  status = "done" then todo.id end)) AS done_count'),
         DB::raw('count( distinct(case when status = "in_progress" then todo.id  end)) AS remaining_count'),
         DB::raw('count( distinct(case when status = "waiting" then todo.id  end)) AS waiting_count'),
            DB::raw('sum(item_actions.time_spent)	as "spent_time" ')

        )
            ->leftJoin('item_actions', 'item_actions.item_id', 'todo.id')
            ->leftJoin('category', 'category.id', 'todo.category_id')
            ->whereRaw($where)
            ->groupBy(['Date', 'category.id'    ])->get();

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

        $columns = array('Date', 'Category', 'number done items', 'number of remaining items', 'number of newly added items',
            'amount of time spent');

        $stream = function () use ($report_items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($report_items as $task) {
                $row['Date'] = $task->Date;
                $row['Category'] = $task->Category;
                $row['number done items'] = $task->done_count;
                $row['number of remaining items'] = $task->remaining_count;
                $row['number of newly added items'] = $task->waiting_count;
                $row['amount of time spent'] = UtilitiesController::secondsToString($task->spent_time);

                fputcsv($file, array($row['Date'], $row['Category'], $row['number done items'],
                    $row['number of remaining items'], $row['number of newly added items'], $row['amount of time spent']));
            }

            fclose($file);
        };
        return response()->stream($stream, 200, $headers);
    }

    public function getReport2Result(Request $request)
    {
        $where = $this->prepareFilter($request);


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

    /**
     * @param Request $request
     * @return string
     */
    public function prepareFilter(Request $request): string
    {
        $where = '1 ';

        if ($request->start_date && !isset($request->end_date))
            $where .= ' and start_time > "' . $request->start_date . '"';

        if ($request->end_date && !isset($request->start_date))
            $where .= ' and start_time < "' . $request->end_date . '"';

        if ($request->end_date && $request->start_date)
            $where .= ' and start_time between "' . $request->start_date . '" and "' . $request->end_date . '" ';
        return $where;
    }
}
