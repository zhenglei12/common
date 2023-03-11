<?php


namespace Zl\Common\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class tmMigrationUpgrade extends Command
{
    protected $signature = 'tm:migration:upgrade ${tag}';
    protected $description = '数据库结构升级 tag: support_parent_subtask';

    public function handle(){
        $tag = $this->argument('tag');
        $this->$tag();
    }

    protected function support_parent_subtask(){
        if(!Schema::hasColumn('tm_messages', 'parent_subtask')){
            Schema::table('tm_messages', function (Blueprint $table) {
                $table->string('parent_subtask',50)->nullable()->index()->after('message_id');
                $this->info('Add column: tm_messages.parent_subtask');
            });
        }

        if(!Schema::hasColumn('tm_processes', 'producer')){
            Schema::table('tm_processes', function (Blueprint $table) {
                $table->string('producer',20)->index()->after('id');
                $this->info('Add column: tm_processes.producer');
            });
        }

        Schema::table('tm_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id')->change();
            $this->info('Change column: tm_messages.message_id to unsignedBigInteger');
        });
     }



}
