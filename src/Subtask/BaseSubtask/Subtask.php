<?php


namespace Zl\Common\Subtask\BaseSubtask;


use Zl\Common\Message\TransactionMessage;
use Zl\Common\YiMqClient;
use Zl\Common\YiMqMessageBuilder;

abstract class Subtask
{
    public $serverType;
    public $type;
    public $id;
    protected $client;
    protected $message;
    protected $data;
    protected $mockManager;
    public $model;
    public $options = [];

    public function __construct(YiMqClient $client,TransactionMessage $message)
    {
        $this->client = $client;
        $this->mockManager = $client->getMockManager();
        $this->message = $message;
    }

    /**
     * @param $data
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;
        return $this;
    }
    public function attempt($attempts){
        $this->options['attempts'] = $attempts;
        return $this;
    }

    public function timeout($milliseconds){
        $this->options['timeout'] = $milliseconds;
        return $this;
    }

    public function getData(){
        return $this->data;
    }
//    abstract public function run();
//    abstract public function getContext();
}