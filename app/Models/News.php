<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
