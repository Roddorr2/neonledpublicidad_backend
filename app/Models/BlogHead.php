<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="BlogHead",
 *     type="object",
 *     title="BlogHead",
 *     description="Encabezado de un blog con título, frase destacada, descripción e imagen",
 *     required={"titulo", "texto_frase", "texto_descripcion", "public_image", "url_image"},
 *     @OA\Property(property="id_blog_head", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="titulo", type="string", example="Título del blog"),
 *     @OA\Property(property="texto_frase", type="string", example="Una frase inspiradora del blog"),
 *     @OA\Property(property="texto_descripcion", type="string", example="Descripción breve del blog para captar atención"),
 *     @OA\Property(property="public_image", type="string", format="url", example="http://localhost:8000/storage/images/blogs/head/imagenPrincipal.webp"),
 *     @OA\Property(property="url_image", type="string", format="url", example="/storage/images/blogs/head/imagenPrincipal.webp"),
 *     @OA\Property(
 *         property="blog",
 *         ref="#/components/schemas/Blog"
 *     )
 * )
 */
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
    ];

    public function blog(){
        return $this->belongsTo(Blog::class, 'id_blog_head', 'id_blog_head');
    }
}
