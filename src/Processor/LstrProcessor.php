<?php


namespace Zl\Common\Processor;


use Zl\Common\Constants\SubtaskStatus;
use Zl\Common\Constants\SubtaskType;
use Zl\Common\Models\ProcessModel;

abstract class LstrProcessor extends EcProcessor
{

    public $serverType = 'LSTR';
    public $type = SubtaskType::LSTR;

    public function getCondition(){
        return null;
    }

    public function getOptions()
    {
        return [
          'condition' => $this->getCondition()
        ];
    }

}