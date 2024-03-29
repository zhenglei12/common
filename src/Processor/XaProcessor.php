<?php


namespace Zl\Common\Processor;


use Zl\Common\Constants\SubtaskServerType;
use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Exceptions\YiMqSystemException;
use Zl\Common\Models\ProcessModel;
use Zl\Common\Processor\BaseProcessor\BaseTccProcessor;

abstract class XaProcessor extends BaseTccProcessor
{
    public $type = SubtaskType::XA;
    private $pdo;
    public $serverType = SubtaskServerType::XA;
    public function __construct()
    {
        $this->pdo = \DB::connection()->getPdo();
    }

    public function _runTry($context){
        //1. 本地记录subtask
        $this->createProcess(SubtaskStatus::PREPARING);
        $this->beforeTransaction();
        //TODO:: 如果子任务已经存在就不开启事务了
        //2. 开启xa事务
//        $this->pdo->exec("set innodb_lock_wait_timeout=1");
        $this->pdo->exec("XA START '$this->id'");
        try{
            $this->setAndlockSubtaskModel();
//            $this->pdo->exec("set innodb_lock_wait_timeout=5");
            $prepareResult = $this->prepare();
            $this->processModel->status = SubtaskStatus::DONE;
            $this->processModel->save();
            //3. prepare xa事务
            $this->pdo->exec("XA END '$this->id'");
            $this->pdo->exec("XA PREPARE '$this->id'");
            $this->afterTransaction();
            return $prepareResult;

        }catch (\Exception $e){
            $this->pdo->exec("XA END '$this->id'");
            $this->pdo->exec("XA ROLLBACK '$this->id'");
            $this->catchTransaction();
            throw $e;
        }


    }

    public function _runConfirm($context){
        try{
            $this->pdo->exec("XA COMMIT '$this->id'");
            return ['message'=>"succeed"];
        }catch (\Exception $e){
            if($e->getCode() != "XAE04"){
                throw  $e;
            }

            //如果不是xa id不存在，就锁定任务记录，判断状态是否已为done
            $this->setAndlockSubtaskModel();
            if($this->processModel->status == SubtaskStatus::DONE){
                return ['message'=>"retry_succeed"];
            }
            $status = $this->statusMap[$this->processModel->status];
            throw new YiMqSystemException("Status is $status.");
        }

    }

    public function _runCancel($context){
        try{
            $this->pdo->exec("XA ROLLBACK '$this->id'");
            $this->setSubtaskStatusCanceled();
            return ['message'=>"canceled"];
        }catch (\Exception $e){
            if($e->getCode() != "XAE04"){
                throw  $e;
            }

            //如果不是xa id不存在，就锁定任务记录，判断状态是否已为done
            //$subTask = $this->setAndlockSubtaskModel();
            $this->processModel =  ProcessModel::lock('for update nowait')->find($this->id);
            if (!$this->processModel) {
                return ['message'=>"not_prepare"];
            }

            if($this->processModel->status == SubtaskStatus::CANCELED){
                return ['message'=>"retry_canceled"];
            }
            if($this->processModel->status == SubtaskStatus::PREPARING){
                $this->setSubtaskStatusCanceled();
                return ['message'=>"compensate_canceled"];
            }
            $status = $this->statusMap[$this->processModel->status];
            throw new YiMqSystemException("Status is $status.");
        }

    }



    abstract function prepare();

}