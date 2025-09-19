<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogHead extends Model
{
    use HasFactory;
    protected $table = 'blog_heads';
    protected $primaryKey = 'id_blog_head';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'texto_frase',
        'texto_descripcion',
        'public_image',
        'url_image',
        'alt',
        'title',
        'meta_title',
        'meta_descripcion'
    ];

    // public function blog(){
    //     return $this->belongsTo(Blog::class, 'id_blog_head', 'id_blog_head');
    // }
    public function blog()
    {
        return $this->hasOne(Blog::class, 'id_blog_head', 'id_blog_head');
    }
}
