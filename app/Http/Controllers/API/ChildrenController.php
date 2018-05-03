<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Children;
use App\ChildrenGoal;
use App\Goal;
use App\ChildrenTask;
use App\ApprovedTask;
use App\Task;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;
use DB;

class ChildrenController extends Controller
{
    public function assignGoalAndTaskToChildren(Request $request)
    {
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(), [
            'goal_id' => 'required',
            'goal_value'  => 'required',
            'number_of_months'  => 'required',
            'tasks' => 'required',
            'user_id' => 'required',
            'children_id' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            date_default_timezone_set('UTC');
            $objChildrenGoal = new ChildrenGoal();
            $objChildrenGoal->goal_id = $request->goal_id;
            $objChildrenGoal->user_id = $request->user_id;
            $objChildrenGoal->children_id = $request->children_id;
            $objChildrenGoal->goal_value = $request->goal_value;
            $objChildrenGoal->number_of_months = $request->number_of_months;
            $objChildrenGoal->created_at = date('Y-m-d H:i:s');
            $objChildrenGoal->updated_at = date('Y-m-d H:i:s');
            $objChildrenGoal->save();

            $tasks = $request->tasks;
            foreach ($tasks as $taskValue) {
                $objChildrenTask = new ChildrenTask();
                $objChildrenTask->goal_id = $request->goal_id;
                $objChildrenTask->task_id = $taskValue['task_id'];
                $objChildrenTask->children_id = $request->children_id;
                $objChildrenTask->periodicity_type = $taskValue['periodicity_type'];
                $objChildrenTask->day_of_week = $taskValue['day_of_week'];
                $objChildrenTask->created_at = date('Y-m-d H:i:s');
                $objChildrenTask->updated_at = date('Y-m-d H:i:s');
                $objChildrenTask->save();
            }
            return response()->json(['success' => "Goal and tasks assigned successfully."], 200);
        }
    }

    public function getDashboardData(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'children_id' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $children_id = $request->children_id;
                $getGoal = DB::select("SELECT c.id AS children_id,c.name AS children_name,c.gender,cg.goal_id,cg.goal_value,cg.number_of_months,cg.created_at,g.goal_name FROM children_goals AS cg JOIN childrens AS c ON cg.children_id = c.id JOIN goals AS g on cg.goal_id = g.id WHERE cg.children_id = {$children_id} AND cg.is_deleted = 0");
                if(sizeof($getGoal) > 0) {
                    foreach($getGoal as $goalValue) {
                        $goalValue->tasks = DB::select("SELECT t.task_name,ct.id AS task_id, ct.periodicity_type, ct.day_of_week FROM children_tasks AS ct JOIN tasks AS t ON t.id = ct.task_id WHERE ct.goal_id = {$goalValue->goal_id} AND ct.children_id = {$children_id}");
                    }
                }
                return response()->json(['success' => $getGoal], 200);
            }
        } catch (\Exception $e) {}
    }

    public function getTaskForApprove(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'children_id' => 'required',
                'date' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $children_id = $request->children_id;
                $date = $request->date;
                $day = date('w',strtotime($date));
                $getTasks = DB::select("SELECT ct.id AS task_id, ct.goal_id, t.task_name, ct.periodicity_type, ct.day_of_week, cg.goal_value, cg.number_of_months FROM children_tasks AS ct JOIN children_goals AS cg ON ct.goal_id = cg.id JOIN tasks AS t ON ct.task_id = t.id WHERE ct.children_id = {$children_id} AND ('{$date}' BETWEEN cg.created_at AND DATE_ADD(cg.created_at, INTERVAL cg.number_of_months MONTH)) AND (ct.periodicity_type = 'D' OR (ct.periodicity_type = 'W' AND ct.day_of_week = '{$day}'))");
                foreach ($getTasks as $taskValue) {
                    $perMonth = $taskValue->goal_value / $taskValue->number_of_months;
                    $perTask = $perMonth / 3;
                    if($taskValue->periodicity_type == 'D') {
                        $totalDay = cal_days_in_month(CAL_GREGORIAN,date('m',strtotime($date)),date('Y',strtotime($date)));
                        $taskValue->task_value = $perTask / $totalDay;
                    } else {
                        $taskValue->task_value = $perTask / 4;
                    }
                    $checkDate = date('Y-m-d',strtotime($date));
                    $check = DB::select("SELECT id,is_parent_approved FROM approved_tasks WHERE task_id = {$taskValue->task_id} AND DATE(task_date_time) = '{$checkDate}'");
                    if(sizeof($check) > 0) {
                        $taskValue->approved_id = $check[0]->id;
                        $taskValue->is_approved = $check[0]->is_parent_approved == 1 ? 2 : 1;
                    } else {
                        $taskValue->is_approved = 0;
                    }
                }
                return response()->json(['success' => $getTasks], 200);
            }
        } catch (\Exception $e) {}
    }

    public function approveTask(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'children_id' => 'required',
                'task_value' => 'required',
                'goal_id' => 'required',
                'task_id' => 'required',
                'is_parent_approved' => 'required',
                'task_date_time' => 'required'
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $children_id = $request->children_id;
                $task_value = $request->task_value;
                $goal_id = $request->goal_id;
                $task_id = $request->task_id;
                $is_parent_approved = $request->is_parent_approved;
                $task_date_time = $request->task_date_time;
                $approved_id = $request->approved_id;
                
                if($is_parent_approved == 1) {
                    ApprovedTask::where('id',$approved_id)->update(['is_parent_approved' => 1]);
                    DB::update("UPDATE children_goals SET total_earned = total_earned + {$task_value} WHERE id = {$goal_id}");
                } else {
                    $objApproveTask = new ApprovedTask();
                    $objApproveTask->task_id = $task_id;
                    $objApproveTask->is_parent_approved = $is_parent_approved;
                    $objApproveTask->task_date_time = $task_date_time;
                    $objApproveTask->save();
                }
                return response()->json(['success' => "Approved successfully"], 200);
            }
        } catch (\Exception $e) {}
    }
}
