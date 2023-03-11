<?php


namespace Zl\Common\Mock\Mockers;


use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Zl\Common\Exceptions\YiMqHttpRequestException;
use Zl\Common\YiMqClient;
use Zl\Common\Models\Subtask as SutaskModel;
class YiMqTccSubtaskMocker extends YiMqMocker
{
    public $statusCode;
    public $data;
    public $processor;
    public function __construct(YiMqClient $client,$processor)
    {
        parent::__construct($client);
        $this->processor = $processor;
    }

    public function reply($statusCode,$data){
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->mockManager->add($this);
    }
    public function getType(){
        return \Zl\Common\Subtask\TccSubtask::class;
    }

    public function checkConditions($object,$conditions=[])
    {
        if($object->processor == $this->processor){
            return true;
        }
        return false;
    }

    public function run()
    {
        $first = SutaskModel::query()->orderByDesc('id')->first();
        $id = $first ?  ++ $first->id : 1;
        switch ($this->statusCode){
            case 200:

                return [
                    'id' =>  $id ,
                    'prepareResult'=>[
                        'status' => $this->statusCode,
                        'data' => $this->data
                    ]
                ];
            case 500:
            case 400:
                return [
                    'id' =>  $id ,
                    'prepareResult'=>[
                        'status' => $this->statusCode,
                        'message'=> "Subtask ($id) prepare failed.",
                        'data' => $this->data
                    ]
                ];

        }
    }
}