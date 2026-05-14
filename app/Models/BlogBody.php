<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogBody extends Model
{
    use HasFactory;

    protected $table = 'blog_bodies';

    protected $primaryKey = 'id_blog_body';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'id_commend_tarjeta',
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
        'flag_galeria',
        'flag_consejos',
        'flag_informacion',
        'titulo_tarjeta',
    ];

    public function blog()
    {
        return $this->hasOne(Blog::class, 'id_blog_body', 'id_blog_body');
    }

    public function commend_tarjeta()
    {
        return $this->hasOne(CommendTarjeta::class, 'id_commend_tarjeta', 'id_commend_tarjeta');
    }

    public function tarjetas()
    {
        return $this->hasMany(Tarjeta::class, 'id_blog_body', 'id_blog_body');
    }
}
