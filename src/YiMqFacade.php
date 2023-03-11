<?php
namespace Zl\Common;
use Illuminate\Support\Facades\Facade;


/**
 * @method static \Zl\Common\YiMqManager  client(string $name)
 * @method static \Zl\Common\Mock\YiMqMockerBuilder  mock()
 * @method static \Zl\Common\Message\TransactionMessage  transaction(string $topic,$callback)
 * @method static \Zl\Common\Message\TransactionMessage  commit()
 * @method static \Zl\Common\Message\TransactionMessage  rollback()
 * @method static \Zl\Common\Subtask\TccSubtask  tcc(string $processor)
 * @method static \Zl\Common\Subtask\EcSubtask  ec(string $processor)
 * @method static \Zl\Common\Subtask\XaSubtask  xa(string $processor)
 *  @method static \Zl\Common\Subtask\BcstSubtask  bcst(string $topic)
 *
 */
class YiMqFacade extends Facade
{
    protected static function getFacadeAccessor() {
        return YiMqManager::class;
    }
}
