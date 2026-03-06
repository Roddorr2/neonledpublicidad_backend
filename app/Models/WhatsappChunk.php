<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'campaign_id',
        'parent_chunk_id',
        'chunk_index',
        'recipients_count',
        'attempts',
        'max_attempts',
        'status',
        'meta',
        'scheduled_at',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'recipients_count' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'chunk_index' => 'integer',
        'campaign_id' => 'integer',
        'parent_chunk_id' => 'integer',
    ];

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isCompleted()
    {
        return in_array($this->status, ['completed', 'sent']);
    }

    public function markProcessing()
    {
        $this->status = 'processing';
        $this->save();
    }
}
