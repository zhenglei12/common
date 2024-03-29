<?php


namespace Zl\Common\Message;


use Zl\Common\Constants\MessageServerType;
use Zl\Common\Constants\MessageStatus;
use Zl\Common\Constants\MessageType;
use Zl\Common\Constants\TransactionMessageAction;
use Zl\Common\Models\Message As MessageModel;
use Zl\Common\YiMqClient;

class TransactionMessage extends Message
{
    public $tccSubtasks = [];
    public $prepareSubtasks = [];
    public $prepared = false;
    public $callback = null;
    public $parent_subtask = null;
    public function __construct(YiMqClient $client, $topic,$callback)
    {
        parent::__construct($client, $topic);
        $this->callback = $callback;
    }

    public function begin(){
        if(is_null($this->callback)){
            return $this->_begin();
        }
        $this->_begin();
        try {
            $result = call_user_func($this->callback);
            $this->commit();
            return $result;
        }catch (\Exception $e){
            $this->rollback();
            throw $e;
        };
    }

    private function _begin():TransactionMessage
    {
        //1. 向协调器注册事物
        $this->create();
        //2. 开启事务
        \DB::beginTransaction();
        //3: 锁定message开始
        $this->start();
        return  $this;
    }

    function parentSubtask($parent_subtask){
        $this->parent_subtask = $parent_subtask->producer .'@'. $parent_subtask->id;
        return $this;
    }

    function create()
    {
        if( $this->client->hasTransactionMessage()){
            throw new \Exception("MicroApi transaction message already exists.");
        }
        $messageInfo = $this->createRemoteTransactionRecrod();
        $this->id = $messageInfo['id'];
        //本地数据库记录事物
        $this->createLocalTransactionRecord($messageInfo);
        $this->client->setTransactionMessage($this);
    }
    function start(){
        $this->model = MessageModel::lockForUpdate()->where('message_id',$this->id)->first();
        $this->local_id = $this->model->id;
    }


    private function createRemoteTransactionRecrod(){
        $context = [
            'topic' => $this->getTopic(),
            'type' => MessageServerType::TRANSACTION,
            'data' => $this->data,
            'delay' => $this->delay,
            'parent_subtask' =>  $this->parent_subtask
        ];
        $mockConditions['action'] = TransactionMessageAction::BEGIN;
        if($this->mockManager->hasMocker($this,$mockConditions)){//TODO 增加一个test环境生效的判断
            return $this->mockManager->runMocker($this,$mockConditions);
        }else{
            return $this->client->callServer('create',$context);
        }

    }
    private function createLocalTransactionRecord(){
        \Log::debug('TransactionMessage ['.$this->client->serviceName.'] -向本地数据库记录事物 '. $this->id);

        $messageModel = new MessageModel();
        $messageModel->message_id = $this->id;
        $messageModel->parent_subtask = $this->parent_subtask;
        $messageModel->status = MessageStatus::PENDING;
        $messageModel->type = MessageType::TRANSACTION;
        $messageModel->topic = $this->topic;
        $messageModel->save();
    }
    public function addTccSubtask($subtask){
        array_push($this->tccSubtasks,$subtask);
    }
    public function addEcSubtask($subtask){
        array_push($this->prepareSubtasks,$subtask);
    }

    private function prepare(){
        $context = [
            'message_id' => $this->id,
            'prepare_subtasks' => []
        ];
        //如果没有ecSubtask就不发起远程调用
        if(count($this->prepareSubtasks) == 0){
            $this->prepared = true;
            return $this;
        }
        foreach ($this->prepareSubtasks as $subtask){
            $prepareSubtask = $subtask->getContext();
            array_push($context['prepare_subtasks'],$prepareSubtask);
        }
        $mockConditions['action'] = TransactionMessageAction::PREPARE;
        if($this->mockManager->hasMocker($this,$mockConditions)){//TODO 增加一个test环境生效的判断
            $result = $this->mockManager->runMocker($this,$mockConditions);
        }else{
            $result = $this->client->callServer('prepare',$context);
        }
        $this->preparedSaveToDb($result);
        $this->prepared = true;
        return $this;
    }
    private function preparedSaveToDb($result){
        foreach ($this->prepareSubtasks as $key => $subtask ){
            $subtask->id = $result['prepare_subtasks'][$key]['id'];
            $subtask->save();
        }
    }

    public function commit(){

        $this->localCommmit();
        \DB::commit();
        $this->client->clearTransactionMessage();
        $this->remoteCommit();
        return $this;
    }

    public function localCommmit(){
        $this->prepare();
        $this->model->status = MessageStatus::DONE;
        $this->model->save();
    }
    private function remoteCommit(){
        try {
            $context['message_id'] = $this->id;
            $mockConditions['action'] = TransactionMessageAction::COMMIT;
            if($this->mockManager->hasMocker($this,$mockConditions)){//TODO 增加一个test环境生效的判断
                $result = $this->mockManager->runMocker($this,$mockConditions);
            }else{
                $result = $this->client->callServer('confirm',$context);
            }
        }catch (\Exception $e){
            //本地commit后，如果远程commit错误，忽略错误,让服务端回查来状态来确认
            \Log::error($e);
        }
    }

    public function rollback(){
        \DB::rollBack();
        $this->client->clearTransactionMessage();
        //本地rollback后，如果远程commit错误，忽略错误,让服务端回查来状态来确认

        $this->remoteRollback();


        return $this;
    }
    public function remoteRollback(){

        try {
            //TODO::添加一个mock锚点，测试rollback后修改message状态失败
            $this->model->status = MessageStatus::CANCELED;
            $this->model->save();
            $context['message_id'] = $this->id;
            $mockConditions['action'] = TransactionMessageAction::ROLLBACK;
            if($this->mockManager->hasMocker($this,$mockConditions)){//TODO 增加一个test环境生效的判断
                $result = $this->mockManager->runMocker($this,$mockConditions);
            }else{
                $result = $this->client->callServer('cancel',$context);
            }
        }catch (\Exception $e){
            \Log::error($e);
        }
    }


}
