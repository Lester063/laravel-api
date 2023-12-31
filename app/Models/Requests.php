<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'idrequester',
        'iditem',
        'statusrequest',
        'isreturnsent'
    ];

    protected $hidden = [
        'password',
        'is_admin'
    ];
}
