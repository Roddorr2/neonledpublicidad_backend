<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="BlogFooter",
 *     type="object",
 *     title="BlogFooter",
 *     required={"titulo", "descripcion"},
 *     @OA\Property(
 *         property="id_blog_footer",
 *         type="integer",
 *         format="int64",
 *         description="ID del blog footer"
 *     ),
 *     @OA\Property(
 *         property="titulo",
 *         type="string",
 *         description="Título del footer"
 *     ),
 *     @OA\Property(
 *         property="descripcion",
 *         type="string",
 *         description="Descripción del footer"
 *     ),
 *     @OA\Property(
 *         property="public_image1",
 *         type="string",
 *         nullable=true,
 *         description="Ruta pública imagen 1"
 *     ),
 *     @OA\Property(
 *         property="url_image1",
 *         type="string",
 *         nullable=true,
 *         description="URL imagen 1"
 *     ),
 *     @OA\Property(
 *         property="public_image2",
 *         type="string",
 *         nullable=true,
 *         description="Ruta pública imagen 2"
 *     ),
 *     @OA\Property(
 *         property="url_image2",
 *         type="string",
 *         nullable=true,
 *         description="URL imagen 2"
 *     ),
 *     @OA\Property(
 *         property="public_image3",
 *         type="string",
 *         nullable=true,
 *         description="Ruta pública imagen 3"
 *     ),
 *     @OA\Property(
 *         property="url_image3",
 *         type="string",
 *         nullable=true,
 *         description="URL imagen 3"
 *     ),
 * )
 */
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
        'public_image2',
        'url_image2',
        'public_image3',
        'url_image3',
    ];

    public function blog(){
        return $this->belongsTo(Blog::class, 'id_blog_footer', 'id_blog_footer');
    }
}
