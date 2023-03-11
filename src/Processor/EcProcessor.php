<?php


namespace Zl\Common\Processor;


use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Models\ProcessModel;
use Zl\Common\Processor\BaseProcessor\Processor;

abstract class EcProcessor extends Processor
{
    public $serverType = 'EC';
    public $type = SubtaskType::EC;
    public $manual_mode = false;
    public function _runConfirm($context)
    {
        $this->setContextToThis($context);

        $subtaskModel =  ProcessModel::find($this->id);
        //如果任务已经存在且已经完成
        if(isset($subtaskModel) && $subtaskModel->status == SubtaskStatus::DONE){
            return ['status'=>"retry_succeed"];
        }

        if(!isset($subtaskModel)){
            $this->createProcess(SubtaskStatus::DOING);
        }

        $this->_runValidate();

        if($this->manual_mode){
             $this->_manualTransaction();
        }else{
             $this->_autoTransaction();
        }

        return ['status'=>"succeed"];


    }
    private function _manualTransaction(){
        $this->do();

    }
    private function _autoTransaction(){
        try{
            \DB::beginTransaction();
            $this->_ec_begin();
            $this->do();
            $this->_ec_done();
            \DB::commit();
        }catch (\Exception $e){
            \DB::rollBack();
            throw $e;
        }
    }
    public function _ec_begin(){
        $this->setAndlockSubtaskModel();
    }
    public  function _ec_done(){
        $this->processModel->status = SubtaskStatus::DONE;
        $this->processModel->save();
    }


    abstract protected function do();
}