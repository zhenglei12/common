<?php

namespace Zl\Common\Exception\Exceptions;
use Exception;

class BusinessException extends Exception
{

    private $_code = 400;

    private $error_data;

    public function __construct($cause = 'BusinessException.', $data=[])
    {
        parent::__construct($cause);

        $this->error_data = $data;
    }

    public function getResponse()
    {
        $ret = [
            'message' => trans($this->getMessage(), $this->error_data),
            'type' => 'business_error',
        ];
        if(!empty($this->error_data)){
            $ret['data'] = $this->error_data;
        }
        return response()->json($ret, $this->_code);
    }
}
