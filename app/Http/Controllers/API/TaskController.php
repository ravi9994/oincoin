<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Children;
use App\Goal;
use App\Task;
use IBL\JWT\Token;
use Illuminate\Http\Request;
use Validator;

class TaskController extends Controller
{
    public function getTasks()
    {
        $token = new Token();
        $user = $token->getUserID();
        $getGoals = Task::all();
        return response()->json(['success' => $getGoals], 200);
    }

    public function addChildTask(Request $request) {
        $token = new Token();
        $user = $token->getUserID();
        
    }
}
