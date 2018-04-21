<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $table = 'goals';
    public $primaryKey = 'id';
    public $timestamps = false;
}
