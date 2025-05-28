<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Blog",
 *     type="object",
 *     title="Blog",
 *     description="Modelo central que une las secciones Head, Body y Footer de un blog",
 *     required={"id_blog_head", "id_blog_body", "id_blog_footer", "fecha"},
 *     @OA\Property(property="id_blog", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="id_blog_head", type="integer", example=10),
 *     @OA\Property(property="id_blog_body", type="integer", example=20),
 *     @OA\Property(property="id_blog_footer", type="integer", example=30),
 *     @OA\Property(property="fecha", type="string", format="date", example="2025-05-22"),
 *     
 *     @OA\Property(property="head", ref="#/components/schemas/BlogHead"),
 *     @OA\Property(property="body", ref="#/components/schemas/BlogBody"),
 *     @OA\Property(property="footer", ref="#/components/schemas/BlogFooter"),
 *     @OA\Property(property="card", ref="#/components/schemas/Card")
 * )
 */
class Blog extends Model
{
    use HasFactory;
    protected $table = 'blogs';
    protected $primaryKey = 'id_blog';
    public $timestamps = false;

    protected $fillable = [
        'link',
        'id_blog_head',
        'id_blog_body',
        'id_blog_footer',
        'fecha'
    ];

    public function head(){
        return $this->hasOne(BlogHead::class, 'id_blog_head', 'id_blog_head');
    }

    public function body(){
        return $this->hasOne(BlogBody::class, 'id_blog_body', 'id_blog_body');
    }

    public function footer(){
        return $this->hasOne(BlogFooter::class, 'id_blog_footer', 'id_blog_footer');
    }

    public function card(){
        return $this->belongsTo(Card::class, 'id_blog', 'id_blog');
    }

}
