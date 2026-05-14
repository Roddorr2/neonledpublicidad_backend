<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommendTarjeta extends Model
{
    use HasFactory;

    protected $table = 'commend_tarjetas';

    protected $primaryKey = 'id_commend_tarjeta';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'texto1',
        'texto2',
        'texto3',
        'texto4',
        'texto5',
    ];

    public function blog_body()
    {
        return $this->belongsTo(BlogBody::class, 'id_commend_tarjeta', 'id_commend_tarjeta');
    }
}
