<?php

namespace App\Exceptions;

use Exception;

class InsufficientGoldException extends QuestException
{
    /**
     * Create a new insufficient gold exception instance.
     */
    public function __construct(int $currentGold, int $requiredGold)
    {
        $message = sprintf(
            'Insufficient gold. You have %s gold coins, but need %s gold coins.',
            number_format($currentGold),
            number_format($requiredGold)
        );
        
        parent::__construct($message, 402);
    }
}

