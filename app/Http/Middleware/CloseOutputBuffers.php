<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloseOutputBuffers
{
    public function handle(Request $request, Closure $next): Response
    {
        $initialBufferLevel = ob_get_level();
        $response = $next($request);

        $this->closeBuffersAbove($initialBufferLevel);

        return $response;
    }

    protected function closeBuffersAbove(int $bufferLevel): void
    {
        while (ob_get_level() > $bufferLevel) {
            $currentLevel = ob_get_level();
            $status = @ob_get_status(true);

            if (empty($status)) {
                break;
            }

            @ob_end_clean();

            if (ob_get_level() >= $currentLevel) {
                break;
            }
        }
    }
}
