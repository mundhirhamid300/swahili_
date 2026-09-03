<?php

/** Hii exception huwakilisha hitilafu maalumu ya programu. */

namespace App\Exceptions;

use Exception;
use Throwable;

class AiServiceException extends Exception
{
    public function __construct(
        string $message = 'AI service unavailable.',
        public readonly ?string $provider = null,
        public readonly ?int $statusCode = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function toUserMessage(): string
    {
        return $this->getMessage();
    }
}
