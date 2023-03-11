<?php


namespace Zl\Common\Console;


use Illuminate\Console\Command;

class YiMqPublishCommand extends Command
{
    protected $signature = 'tm:publish';
    protected $description = '配置发布';

    protected $processors = [];
    protected $broadcastTopics = [];
    protected $broadcastListeners = [];
    protected $schedules = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){
        $config = config('tm.processors');
        dump($config);
    }

}
