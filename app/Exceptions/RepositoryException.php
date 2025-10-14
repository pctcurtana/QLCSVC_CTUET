<?php

namespace App\Exceptions;

use Exception;

class RepositoryException extends Exception
{
    /**
     * Tạo exception cho Repository layer
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "Lỗi xảy ra khi truy vấn dữ liệu", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

