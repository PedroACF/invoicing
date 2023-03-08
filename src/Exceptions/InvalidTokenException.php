<?php

namespace PedroACF\Invoicing\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class InvalidTokenException extends Exception
{
    public function report(): void
    {
        // ...
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response(/* ... */);
    }
}
