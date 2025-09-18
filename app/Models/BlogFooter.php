<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogFooter extends Model
{
    use HasFactory;

    protected $table = 'blog_footers';
    protected $primaryKey = 'id_blog_footer';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'public_image1',
        'url_image1',
        'alt_image1',
        'title_image1',
        'public_image2',
        'url_image2',
        'alt_image2',
        'title_image2',
        'public_image3',
        'url_image3',
        'alt_image3',
        'title_image3',
        'estado',
    ];

    public function blog(){
        return $this->belongsTo(Blog::class, 'id_blog_footer', 'id_blog_footer');
    }
}
