<?php

declare(strict_types=1);

namespace Werty\Http\Json;

class Exception extends \Exception
{
    private $request;
    private $response;

    public function __construct($message = "", $code = 0, $request = '', $response = '')
    {
        parent::__construct($message, $code);

        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

}
