<?php
namespace App\Exceptions;
use Exception;

class StudentException extends Exception
{
    public function __construct(string $message = "Student error", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'error' => true,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }
}
