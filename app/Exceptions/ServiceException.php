<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    /**
     * Tạo exception cho Service layer
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "Lỗi xảy ra khi xử lý nghiệp vụ", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

