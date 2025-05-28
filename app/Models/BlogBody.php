<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="BlogBody",
 *     type="object",
 *     title="BlogBody",
 *     description="Sección central del contenido del blog, que puede incluir imágenes, una tarjeta destacada y una colección de tarjetas",
 *     required={"titulo", "descripcion"},
 *     @OA\Property(property="id_blog_body", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="titulo", type="string", example="Explorando el Futuro de la Tecnología"),
 *     @OA\Property(property="descripcion", type="string", example="Un análisis profundo sobre el impacto de la IA en la vida cotidiana."),
 *     @OA\Property(property="id_commend_tarjeta", type="integer", nullable=true, example=5),

 *     @OA\Property(property="public_image1", type="boolean", example=true),
 *     @OA\Property(property="url_image1", type="string", format="uri", example="https://example.com/image1.jpg"),
 *     @OA\Property(property="public_image2", type="boolean", example=false),
 *     @OA\Property(property="url_image2", type="string", format="uri", example="https://example.com/image2.jpg"),
 *     @OA\Property(property="public_image3", type="boolean", example=true),
 *     @OA\Property(property="url_image3", type="string", format="uri", example="https://example.com/image3.jpg"),

 *     @OA\Property(property="commend_tarjeta", ref="#/components/schemas/CommendTarjeta"),
 *     @OA\Property(property="tarjetas", type="array", @OA\Items(ref="#/components/schemas/Tarjeta"))
 * )
 */
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
        'public_image2',
        'url_image2',
        'public_image3',
        'url_image3',
    ];

    public function blog(){
        return $this->belongsTo(Blog::class, 'id_blog_body', 'id_blog_body');
    }

    public function commend_tarjeta(){
        return $this->hasOne(CommendTarjeta::class, 'id_commend_tarjeta', 'id_commend_tarjeta');
    }

    public function tarjetas(){
        return $this->hasMany(Tarjeta::class, 'id_blog_body', 'id_blog_body');
    }
}
