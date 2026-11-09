<?php

namespace App\Http\Middleware;

use App\Support\Hybrid;
use Closure;
use Illuminate\Http\Request;

/**
 * A hybrid class's page (App\Support\Hybrid): the texts marked "both
 * languages" become the language to learn with English smaller after it,
 * or "Target (English)" where HTML can't go (attributes, scripts, JSON).
 */
class RenderHybridText
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $content = method_exists($response, 'getContent') ? $response->getContent() : false;
        if (is_string($content) && Hybrid::has($content)) {
            $type = (string) $response->headers->get('Content-Type');
            $response->setContent(str_contains($type, 'html') || $type === '' ? Hybrid::html($content) : Hybrid::plain($content));
        }

        Hybrid::enable(false);

        return $response;
    }
}
