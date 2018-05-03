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
            $objGoal->created_at = date('Y-m-d H:i:s');
            $objGoal->updated_at = date('Y-m-d H:i:s');
            $objGoal->save();
            return response()->json(['success' => $objGoal], 200);
        }
    }

    public function getGoal(Request $request){
        $token = new Token();
        $user = $token->getUserID();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages[0]], 200);
        } else {
            $getGoals = Goal::where('user_id',$request->user_id)->orWhere('user_id',null)->get();
            return response()->json(['success' => $getGoals], 200);
        }
    }

    public function updateGoalIcon(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'goal_id' => 'required',
                'goal_icon' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $goal_id = $request->goal_id;
                $image = $request->file('goal_icon');
                $imageFileName = str_random(16).'.png';
                $image->move( public_path('/uploads/'), $imageFileName);
                Goal::where('id',$goal_id)->update(['goal_icon'=>$imageFileName]);
                $imgUrl = URL('/uploads/')."/".$imageFileName;
                return response()->json(['success' => $imgUrl], 200);
            }
        } catch (\Exception $e) {}
    }
}
