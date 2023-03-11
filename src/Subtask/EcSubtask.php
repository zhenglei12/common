<?php


namespace Zl\Common\Subtask;

use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Models\Subtask as SubtaskModel;
use Zl\Common\Subtask\BaseSubtask\ProcessorSubtask;

class EcSubtask extends ProcessorSubtask
{
    public $serverType = "EC";
    public $type = SubtaskType::EC;

    public function join()
    {
        $this->message->addEcSubtask($this);
        return $this;
    }

    public function save(){
        $this->model = new SubtaskModel();
        $this->model->subtask_id = $this->id;
        $this->model->message_id = $this->message->id;
        $this->model->status = SubtaskStatus::PREPARED;
        $this->model->type = $this->type;
        $this->model->save();
    }
    public function getContext(){
        return [
            'type'=> $this->serverType,
            'processor'=>$this->processor,
            'data'=> $this->getData(),
            'options'=>$this->options
        ];
    }
}