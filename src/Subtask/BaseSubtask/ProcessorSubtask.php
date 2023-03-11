<?php


namespace Zl\Common\Subtask\BaseSubtask;


use Zl\Common\Message\TransactionMessage;
use Zl\Common\YiMqClient;
use Zl\Common\YiMqMessageBuilder;

abstract class ProcessorSubtask extends Subtask
{

    public $processor;


    public function __construct(YiMqClient $client,TransactionMessage $message, $processor)
    {
        parent::__construct($client, $message);
        $this->processor = $processor;
    }
}