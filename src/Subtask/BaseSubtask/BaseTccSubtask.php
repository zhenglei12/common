<?php


namespace Zl\Common\Subtask\BaseSubtask;


use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Exceptions\YiMqHttpRequestException;
use Zl\Common\Exceptions\YiMqSubtaskPrepareException;
use Zl\Common\Models\Subtask as SubtaskModel;

class BaseTccSubtask extends ProcessorSubtask
{
    public $prepareStatus;
    public $prepareMessage;
    public $prepareData;
    public $throw = true;

    public function _try()
    {
        $result = $this->callServer();
        $this->id = $result['id'];

        $prepareResult = $result['prepareResult'];
        $this->prepareStatus = $prepareResult['status'];
        $this->prepareData = isset($prepareResult['data'])?$prepareResult['data']:null;

        if($this->prepareSuccessful() == false){
            $this->prepareMessage =  $prepareResult['message'];

            if($this->throw){
                throw new YiMqSubtaskPrepareException($this,$this->prepareMessage,$this->prepareData,$this->prepareStatus);
            }else{
                return $this;
            }
        }





        $this->model = new SubtaskModel();
        $this->model->subtask_id = $this->id;
        $this->model->message_id = $this->message->id;
        $this->model->status = SubtaskStatus::PREPARED;
        $this->model->type = $this->type;
        $this->model->save();

        $this->message->addTccSubtask($this);
        return $this;
    }

    public function prepareSuccessful(){
        return $this->prepareStatus == 200 ? true : false;
    }
    public function callServer(){
        if($this->mockManager->hasMocker($this)){//TODO 增加一个test环境生效的判断
            return $this->mockManager->runMocker($this);
        }else{
            return $this->client->callServer('subtask',$this->getContext());
        }
    }

    public function throw($throw=true){
        $this->throw = $throw;
        return $this;
    }

    public function getContext()
    {
        return [
            'message_id' => $this->message->id,
            'type' => $this->serverType,
            'processor' => $this->processor,
            'data' => $this->data,
            'options'=>$this->options
        ];
    }

}