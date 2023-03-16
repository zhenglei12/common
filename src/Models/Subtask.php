<?php


namespace Zl\Common\Models;


use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $table = 'tm_subtasks';
    protected $fillable = [
        'id',
        'subtask_id',
        'message_id',
        'type',
        'data',
        'status'
    ];
    protected $casts = [
        'data' => 'json'
    ];
}
