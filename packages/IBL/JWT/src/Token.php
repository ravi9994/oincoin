<?php

namespace IBL\JWT;

use Firebase\JWT\JWT;
use IBL\JWT\src\Model\JwtToken;
use DB;

/**
 * AUTH
 */
const AUTH_JWT_SECRET = "METCHELITenTiVErodsAbiNCHwARIERFaEsITHuSeVNdvLNuTrPtltlPgtnMnld";

JWT::$leeway = 36000000;

class Token {

    private $token;
    private $decoded;

    private $requestId;
    private $logger;

    function __construct() {
        $this->retrieveToken();
        $this->validateToken();
    }

    function retrieveToken() {
        // This method will exist if you're using apache
        // If you're not, please go to the extras for a definition of it.
        $requestHeaders = apache_request_headers();
        if(isset($requestHeaders['Authorization']))
        {
            $authorizationHeader = $requestHeaders['Authorization'] ?: $requestHeaders['authorization'];
            $this->token = str_replace('Bearer ', '', $authorizationHeader);
        }
        else
        {
            $this->unauthorizedError('Authorization Header Not Found');
        }
    }

    function validateToken() {
        try {
            $this->decoded = JWT::decode($this->token, AUTH_JWT_SECRET, array('HS256'));
        } catch (\Exception $e) {
            $message = 'Unauthorized';
            $this->unauthorizedError($message);
        }
    }

    function unauthorizedError($message) {
        header('HTTP/1.0 401 Unauthorized');
        $response['status_code'] = 401;
        $response['response_code'] = 1;
        $response['response_message'] = $message;
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    function forbiddenError() {
        header('HTTP/1.0 403 Forbidden');
        $msg = "No access level for user.";

        $response['status'] = 1;
        $response['msg'] = $msg;
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    public function getUserID() {
        if ($this->decoded->userID == null) {
            $this->forbiddenError();
        }else{
            $secret = $this->decoded->token;
            $user_id = $this->decoded->userID;
            $jwt_token = JwtToken::where('secret',$secret)->where('user_id',$user_id)->where('revoked',0)->first();
            if(empty($jwt_token)){
                $this->forbiddenError();
            }else{
                $user = DB::table($jwt_token->table_name)->where('id',$user_id)->select('*')->first();
                return $user;
            }
        }
    }

    public function logout(){
        if ($this->decoded->userID == null) {
            $this->forbiddenError();
        }else{
            $secret = $this->decoded->token;
            $user_id = $this->decoded->userID;
            $jwt_token = JwtToken::where('secret',$secret)->where('user_id',$user_id)->where('revoked',0)->first();
            if(empty($jwt_token)){
                $this->forbiddenError();
            }else{
                $jwt_token->revoked = 1;
                $jwt_token->save();
                return true;
            }
            return false;
        }
    }

    public static function getAuthUserToken($userID,$table_name) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 15; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $token = array(
            "iss" => "GoLEAGUE_API_V2",
            "origin" => "php",
            "userID" => $userID,
            "token" => $randomString
        );
        $user = DB::table($table_name)->where('id',$userID)->select('*')->first();

        $JwtToken = new JwtToken();
        $JwtToken->user_id = $userID;
        $JwtToken->secret = $randomString;
        $JwtToken->table_name = $table_name;
        // $JwtToken->name = $user->email;
        $JwtToken->revoked = 0;
        $JwtToken->save();

        $jwt = JWT::encode($token, AUTH_JWT_SECRET);

        return $jwt;
    }

}