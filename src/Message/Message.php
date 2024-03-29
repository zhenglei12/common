<?php


namespace Zl\Common\Message;


use Zl\Common\YiMqBuilder;
use Zl\Common\YiMqClient;
use Zl\Common\YiMqMessageBuilder;

abstract class Message
{
    protected $client;
    public $topic;
    public  $model;
    public $id;
    public $local_id;
    public $mockManager;
    public $delay = 5000;
    public $data;
    public function __construct(YiMqClient $client,$topic)
    {
        $this->client = $client;
        $this->mockManager = $client->getMockManager();
        $this->topic = $topic;
    }
    public function delay($millisecond){
        $this->delay = $millisecond;
        return $this;
    }
    public function data($data){
        $this->data = $data;
        return $this;
    }

    abstract function create();

    public function getTopic(){
        if(is_null($this->topic)){
            throw new \Exception('Topic not set.');
        }
        return $this->topic;
    }

}