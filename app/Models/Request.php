<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'Requests';
    protected $fillable = ['name','email','status','message','comment','created_at','updated_at'];
}
