<?php
/**
 * Created by PhpStorm.
 * User: lishu
 * Date: 2018/10/11
 * Time: 15:39
 */
namespace Zl\Common\Exception;

use Zl\Common\Exception\Exceptions\BusinessException;

class ExceptionFactory
{
    public function business($cause,$data = []){
        return new BusinessException($cause,$data);
    }

}