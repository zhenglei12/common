<?php


namespace Zl\Common\Subtask;

use Zl\Common\Constants\SubtaskServerType;
use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Message\TransactionMessage;
use Zl\Common\Models\Subtask as SubtaskModel;
use Zl\Common\Subtask\BaseSubtask\Subtask;
use Zl\Common\YiMqClient;

class BcstSubtask extends Subtask
{
    public $serverType = SubtaskServerType::BCST;
    public $type = SubtaskType::BCST;
    public $topic;

    public function __construct(YiMqClient $client, TransactionMessage $message,$topic)
    {
        parent::__construct($client, $message);
        $this->topic = $topic;
    }

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
            'topic'=>$this->topic,
            'data'=> $this->getData(),
            'options'=>$this->options
        ];
    }


}