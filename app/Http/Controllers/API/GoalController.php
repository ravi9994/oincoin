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
        
    }
}
