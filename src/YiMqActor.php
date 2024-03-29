<?php


namespace Zl\Common;



use Zl\Common\Constants\MessageServerStatus;
use Zl\Common\Constants\MessageStatus;
use Zl\Common\Constants\SubtaskServerType;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Exceptions\YiMqSystemException;
use Zl\Common\Models\Message as  MessageModel;
use Zl\Common\Models\ProcessModel;

class YiMqActor
{
    private $processorsMap;
    private $listenersMap;
    private $app;
    public function __construct(\App $app)
    {
        $this->app = $app;
        $this->processorsMap = config('yimq.processors');
        $this->listenersMap = config('yimq.broadcast_listeners');

    }

    public function try($context){
        \Log::debug('try',$context);
        $processor = $this->getProcessor($context);
        if($processor == null){
            $processor = $context['processor'];
            throw new YiMqSystemException("Processor <$processor> not exists");
        }
        return $processor->runTry($context);

    }

    public function confirm($context){
        \Log::debug('confirm',$context);
        $processor = $this->getProcessor($context);
        if($processor == null){
            $processor = $context['processor'];
            throw new YiMqSystemException("Processor <$processor> not exists");
        }
        return $processor->runConfirm($context);
    }

    public function cancel($context){
        $processor = $this->getProcessor($context);
        if($processor == null){
            return ['message'=>"succeed"];
        }
        return $processor->runCancel($context);
    }

    public function messageCheck($context){
//        $messageModel = MessageModel::lockForUpdate()->where('message_id',$context['message_id'])->first();
        \Log::info("messageCheck", [$context]);
        $messageModel = MessageModel::lock('for update nowait')->where('message_id',$context['message_id'])->first();
        if(!isset($messageModel)){
            // return ['status'=> MessageServerStatus::CANCELED];
           throw new YiMqSystemException('Message not exists.');
        }
        if($messageModel->status == MessageStatus::DONE){
            return ['status'=>'DONE'];
        }
        if($messageModel->status == MessageStatus::CANCELED){
            return ['status'=>'CANCELED'];
        }
        //如果lockForUpdate能拿到message且处于PENDING状态，说明本地回滚后设置 message状态失败，check的时候补偿状态
        if($messageModel->status == MessageStatus::PENDING){
            $messageModel->status = MessageStatus::CANCELED;
            $messageModel->save();
            return ['status'=>'CANCELED','message'=>'compensate canceled'];
        }
    }


    private function getProcessor($context){


        if($context['type'] == SubtaskServerType::LSTR){
            $processor = $context['processor'];
            if(!isset($this->listenersMap[$processor])){
//                throw new YiMqSystemException("Processor <$processor> not exists");
                return null;
            }
            if($context['topic'] != $this->listenersMap[$processor]){
                $contextTopic = $context['topic'];
                $localTopic = $this->listenersMap[$processor];
                throw new YiMqSystemException("Processor '$processor' => '$localTopic' can not process '$contextTopic'.");
            }
            return resolve($processor);
        }else if(in_array($context['type'],[SubtaskServerType::XA,SubtaskServerType::TCC,SubtaskServerType::EC])) {
            $processor = $context['processor'];

            if(!isset($this->processorsMap[$processor])){
//                throw new YiMqSystemException("Processor <$processor> not exists");
                return null;
            }
            return resolve($this->processorsMap[$processor]);
        }else{
            throw new YiMqSystemException("Subtask type ". $context['type'] . 'not suport.');
        }


    }
}
