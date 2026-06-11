<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    use HasFactory;

    protected $table = 'tarjetas';

    protected $primaryKey = 'id_tarjeta';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'id_blog_body',
        'keyword',
        'link',
    ];

    public function blog_body()
    {
        return $this->belongsTo(BlogBody::class, 'id_blog_body', 'id_blog_body');
    }
}
