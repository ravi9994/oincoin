<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Children;
use App\ChildrenGoal;
use App\Goal;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;
class GoalController extends Controller
{
    public function addGoal(Request $request) {
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(),[
            'goal_name'  => 'required',
            'goal_value'  => 'required',
            'number_of_months'  => 'required',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            date_default_timezone_set('UTC');
            $objGoal = new Goal();
            $objGoal->user_id = $request->user_id;
            $objGoal->goal_name = $request->goal_name;
            $objGoal->goal_value = $request->goal_value;
            $objGoal->number_of_months = $request->number_of_months;
            $objGoal->created_at = date('Y-m-d H:i:s');
            $objGoal->updated_at = date('Y-m-d H:i:s');
            $objGoal->save();
            return response()->json(['success' => $objGoal], 200);
        }
    }
}
