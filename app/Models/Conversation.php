<?php

namespace App\Models;

use App\Slavytuch\Telegram\Conversation\Enums\Status;
use App\Slavytuch\Telegram\Conversation\Enums\Topic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id',
        'topic',
        'status'
    ];

    protected $casts = [
        'status' => Status::class,
        'topic' => Topic::class
    ];

    public function history()
    {
        return $this->hasMany(ConversationHistory::class);
    }
}
