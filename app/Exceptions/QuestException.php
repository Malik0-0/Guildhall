<?php

namespace App\Exceptions;

use Exception;

class QuestException extends Exception
{
    /**
     * Create a new quest exception instance.
     */
    public function __construct(string $message = 'Quest operation failed', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
            ], $this->getCode());
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}

