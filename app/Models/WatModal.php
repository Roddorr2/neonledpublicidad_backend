<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WatModal extends Model
{
    use HasFactory;

    protected $table = 'modal_wats';
    protected $primaryKey = 'id_modal_wat';
    public $timestamps = false;

    protected $fillable = [
        'estado',
        'error',
        'id_modalservicio',
        'number_message',
        'fecha',
        'message_id',
        'attempts',
        'id_plantilla_whatsapp',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'attempts' => 'integer',
    ];

    public function modalservicio(){
        return $this->belongsTo(modalservicios::class,'id_modalservicio', 'id_modalservicio');
    }
}
