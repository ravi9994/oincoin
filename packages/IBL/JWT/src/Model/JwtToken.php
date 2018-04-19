<?php 

namespace IBL\JWT\src\Model;

use Illuminate\Database\Eloquent\Model;

class JwtToken extends Model {

    public $primaryKey = 'id';
    protected $table = 'jwt_access_token';
    
}