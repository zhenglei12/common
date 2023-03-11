<?php


namespace Zl\Common\Mock\Mockers;


class YiMqXaSubtaskMocker extends YiMqTccSubtaskMocker
{
    public function getType(){
        return \Zl\Common\Subtask\XaSubtask::class;
    }
}