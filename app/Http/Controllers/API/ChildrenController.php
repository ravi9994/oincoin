<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Children;
use App\ChildrenGoal;
use App\Goal;
use App\ChildrenTask;
use App\Task;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;

class ChildrenController extends Controller
{
    public function assignGoalAndTaskToChildren(Request $request)
    {
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(), [
            'goal_id' => 'required',
            'task_id' => 'required',
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
            $objChildrenGoal->created_at = date('Y-m-d H:i:s');
            $objChildrenGoal->updated_at = date('Y-m-d H:i:s');
            $objChildrenGoal->save();

            $tasks = explode(',',$request->task_id);
            foreach ($tasks as $task_id) {
                $objChildrenTask = new ChildrenTask();
                $objChildrenTask->goal_id = $request->goal_id;
                $objChildrenTask->task_id = $task_id;
                $objChildrenTask->created_at = date('Y-m-d H:i:s');
                $objChildrenTask->updated_at = date('Y-m-d H:i:s');
                $objChildrenTask->save();
            }
            return response()->json(['success' => "Goal and tasks assigned successfully."], 200);
        }
    }
}
