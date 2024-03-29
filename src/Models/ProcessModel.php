<?php


namespace Zl\Common\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessModel extends Model
{
    protected $table = 'tm_processes';
    protected $fillable = [
        'id',
        'producer',
        'message_id',
        'type',
        'processor',
        'data',
        'try_result',
        'status'
    ];
    protected $casts = [
        'data' => 'json',
        'try_result' => 'json'
    ];
}
