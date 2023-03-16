<?php


namespace Zl\Common\Models;


use Illuminate\Database\Eloquent\Model;
use Zl\Common\Constants\MessageServerType;
use Zl\Common\Constants\MessageType;

class Message extends Model
{
    protected $table = 'tm_messages';
    protected $fillable = [
        'id',
        'message_id',
        'parent_subtask',
        'topic',
        'type',
        'data',
        'status',
    ];
    protected $casts = [
        'data' => 'json'
    ];
}
