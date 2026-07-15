<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonio extends Model
{
    use HasFactory;

    protected $table = 'testimonios';

    protected $fillable = [
        'nombre',
        'texto',
        'rating',
        'fecha',
        'avatar_url',
        'avatar_public_id',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'rating' => 'integer',
        'orden'  => 'integer',
        'fecha'  => 'date:Y-m-d',
    ];
}