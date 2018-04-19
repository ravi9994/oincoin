<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Children;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use IBL\JWT\Token;
use IBL\JWT\src\Model\JwtToken;

class UserController extends Controller
{
    public function register(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                'responsible_name'  => 'required',
                'children_data'  => 'required'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $check = User::where('email',$request->email)->first();
                if(empty($check)){
                    $accessCode = $this->generateAccessCode();
                    $objUser = new User();
                    $objUser->responsible_name = $request->responsible_name;
                    $objUser->access_code = $accessCode;
                    $objUser->email = $request->email;
                    $objUser->password = bcrypt($request->password);
                    $objUser->phone_number = $request->phone_number;
                    $objUser->facebook_id = $request->facebook_id;
                    if($objUser->save()) {
                        $childrenData = $request->children_data;
                        foreach ($childrenData as $childrenValue) {
                            $objChildern = new Children();
                            $objChildern->parent_id = $objUser->id;
                            $objChildern->name = $childrenValue['name'];
                            $objChildern->gender = $childrenValue['gender'];
                            $objChildern->save();
                        }
                        $success['access_code'] = $accessCode;
                        return response()->json(['success' => $success], 200);
                    } else {
                        return response()->json(['error' => "Something went wrong."], 200);
                    }
                } else {
                    return response()->json(['error' => "Email already exist."], 200);
                }
            }
        } catch (\Exception $e){}
    }

    public function login(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'login_type' => 'required'
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                return response()->json(['error' => $messages[0]], 200);
            } else {
                $loginType = $request->login_type;
                switch ($loginType) {
                    case 1:
                    // login with code
                    $accessCode = $request->access_code;
                    $getUser = User::where('access_code',$accessCode)->first();
                    if(!empty($getUser)) {
                        $getUser->children_data = Children::selectRaw('id, name, gender')->where('parent_id',$getUser->id)->get();
                        $success['response_data'] = $getUser;
                        $success['token'] = Token::getAuthUserToken($getUser->id, 'users');
                        return response()->json(['success' => $success], 200);
                    } else {
                        return response()->json(['error' => 'Unauthorised'], 401);
                    }
                    break;
                    case 2:
                    // login with email password
                    $email = $request->email;
                    $password = $request->password;
                    $getUser = User::where('email', $email)->first();
                    if (!empty($getUser) && Hash::check($request->password, $getUser->getAuthPassword())) {
                        $getUser->children_data = Children::selectRaw('id, name, gender')->where('parent_id', $getUser->id)->get();
                        $success['response_data'] = $getUser;
                        $success['token'] = Token::getAuthUserToken($getUser->id, 'users');
                        return response()->json(['success' => $success], 200);
                    } else {
                        return response()->json(['error' => 'Wrong email address or password'], 401);
                    }
                    break;
                    case 3:
                    // login with facebook
                    $facebookId = $request->facebook_id;
                    $getUser = User::where('facebook_id', $facebookId)->first();
                    if (!empty($getUser)) {
                        $getUser->children_data = Children::selectRaw('id, name, gender')->where('parent_id', $getUser->id)->get();
                        $success['response_data'] = $getUser;
                        $success['token'] = Token::getAuthUserToken($getUser->id, 'users');
                        return response()->json(['success' => $success], 200);
                    } else {
                        $success['is_new'] = 1;
                        return response()->json(['success' => $success], 200);
                    }
                    break;
                }
            }
        } catch (\Exception $e){}
    }

    public function details() {
        $token = new Token();
        $user = $token->getUserID();
        return response()->json(['success' => $user], 200);
    }

    function generateAccessCode() {
        $accessCode = rand (1000, 9999);
        $check = User::where('access_code',$accessCode)->first();
        if(!empty($check)) {
            $this->generateAccessCode();
        } else {
            return $accessCode;
        }
    }
}