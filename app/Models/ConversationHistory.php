<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'last_stage',
        'response',
        'next_stage',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
