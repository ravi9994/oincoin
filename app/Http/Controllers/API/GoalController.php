<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Children;
use App\Goal;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;
class GoalController extends Controller
{
    public function getGoals()
    {
        $token = new Token();
        $user = $token->getUserID();
        $getGoals = Goal::all();
        return response()->json(['success' => $getGoals], 200);
    }

    public function addChildrenGoal(Request $request) {
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(),[
            'goal_name'  => 'required',
            'goal_value'  => 'required',
            'number_of_months'  => 'required',
            'user_id' => 'required',
            'children_id' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            $objGoal = new Goal();
            $objGoal->user_id = $request->user_id;
            $objGoal->children_id = $request->children_id;
            $objGoal->goal_name = $request->goal_name;
            $objGoal->goal_value = $request->goal_value;
            $objGoal->number_of_months = $request->number_of_months;
            $objGoal->save();
            return response()->json(['success' => $objGoal], 200);
        }
    }
}
