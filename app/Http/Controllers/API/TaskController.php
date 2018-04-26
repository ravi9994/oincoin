<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Children;
use App\Goal;
use App\ChildrenTask;
use App\Task;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;

class TaskController extends Controller
{
    public function addTask(Request $request) {
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'task_name' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            date_default_timezone_set('UTC');
            $objTask = new Task();
            $objTask->user_id = $request->user_id;
            $objTask->task_name = $request->task_name;
            $objTask->created_at = date('Y-m-d H:i:s');
            $objTask->updated_at = date('Y-m-d H:i:s');
            $objTask->save();
            return response()->json(['success' => $objTask], 200);
        }
    }

    public function getTask(Request $request){
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            $getTasks = Task::where('user_id',$request->user_id)->orWhere('user_id',null)->get();
            return response()->json(['success' => $getTasks], 200);
        }
    }
}
