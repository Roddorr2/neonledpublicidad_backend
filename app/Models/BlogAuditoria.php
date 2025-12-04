<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogAuditoria extends Model
{
    use HasFactory;

    protected $table = 'blog_auditoria';
    protected $primaryKey = 'id_blog_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'id_blog',
        'id_empleado',
        'accion',
        'titulo', //new
        'descripcion',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'id_blog', 'id_blog');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
