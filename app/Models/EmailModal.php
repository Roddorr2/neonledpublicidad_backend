<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailModal extends Model
{
    use HasFactory;

    protected $table = 'modal_emails';
    protected $primaryKey = 'id_modal_email';
    protected $fillable = [
        'estado',
        'error',
        'id_modalservicio',
        'number_message',
        'fecha',
    ];
    protected $casts = [
        'estado' => 'boolean',
    ];

    public $timestamps = false;

    public function modalServicio(){
        return $this->belongsTo(modalservicios::class,'id_modalservicio', 'id_modalservicio');
    }
}
