<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

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

    public function setDescriptionAttribute($value)
{
    $this->attributes['description'] = Crypt::encryptString($value);
}

public function getDescriptionAttribute($value)
{
    try {
        return Crypt::decryptString($value);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        return $value;
    }
}
}