<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovedTask extends Model
{
    protected $table = 'approved_tasks';
    public $primaryKey = 'id';
    public $timestamps = false;
}
