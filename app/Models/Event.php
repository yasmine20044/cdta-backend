<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'location',
        'category',
        'image'
    ];
}