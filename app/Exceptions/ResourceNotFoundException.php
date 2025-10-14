<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
    /**
     * Tạo exception khi không tìm thấy resource
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "Không tìm thấy dữ liệu", $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

