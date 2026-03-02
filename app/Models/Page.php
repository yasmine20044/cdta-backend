<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'image'
        
    ];

    public function setContentAttribute($value)
{
    $this->attributes['content'] = Crypt::encryptString($value);
}

public function getContentAttribute($value)
{
    try {
        return Crypt::decryptString($value);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        return $value;
    }
}
}