<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloudinaryUpload extends Model
{
    protected $table = 'cloudinary_uploads';

    protected $fillable = [
        'public_id', 'secure_url', 'user_id', 'used', 'expires_at', 'metadata',
    ];

    protected $casts = [
        'used'       => 'boolean',
        'metadata'   => 'array',
        'expires_at' => 'datetime',
    ];
}
