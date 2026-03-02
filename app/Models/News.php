<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'slug',
    'content',
    'image',
    'status',
    'excerpt',
    'author',
    'published_at',
    'category'
];
 
public function SetContentAttribute($value)
{
    $this->attributes['content'] = Crypt::encryptString($value);

}

public function getContentAttribute($value)
{

try {
    return Crypt::decryptString($value);
}catch (DecryptException $e) {
   return $value;
}
}

public function SetExcerptAttribute($value)
{
    $this->attributes['excerpt'] = Crypt::encryptString($value);
}

public function getExcerptAttribute($value)
{
try {    return Crypt::decryptString($value);
}catch (DecryptException $e) {
    return $value;
}
}

}
