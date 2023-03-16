<?php


namespace Zl\Common\Services;


use Zl\Common\Exceptions\YiMqSystemException;

class YiMqActorConfig
{
    protected $processors = [];
    protected $broadcastTopics = [];
    protected $broadcastListeners = [];
    protected $schedules = [];
    function get(){
        if(!is_array(config('tm.processors'))){
            throw new YiMqSystemException('tm config processors define error.');
        }
        foreach (config('tm.processors') as $alias => $processorClass){
            $processorObject = resolve($processorClass);
            $item = $processorObject->getOptions();
            $item['processor'] = config('tm.actor_name').'.'.$alias;
            array_push($this->processors,$item);
        }

        if(!is_array(config('tm.broadcast_listeners'))){
            throw new YiMqSystemException('tm config broadcast_listeners define error.');
        }
        foreach (config('tm.broadcast_listeners',[]) as $class => $topic){
            $listenerObject =  resolve($class);
            $item['processor'] = $class;
            $item['topic'] = $topic;
            $item = array_merge($item,$listenerObject->getOptions());
            array_push($this->broadcastListeners,$item);
        }

        return [
            'actor_name' => config('tm.actor_name'),
            'processors' => $this->processors,
            'broadcast_listeners' => $this->broadcastListeners
        ];




    }

}