<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'location',
        'category',
        'priority',
        'status',
        'technician_id',
        'solution'
    ];
}
