<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust proxy chain from reverse proxy / Cloudflare when configured.
     *
     * Set TRUSTED_PROXIES to "*" in production behind Cloudflare.
     * Keep it empty in local Laragon (no proxy).
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        $trusted = env('TRUSTED_PROXIES');

        if (is_string($trusted) && trim($trusted) !== '') {
            $trusted = trim($trusted);
            $this->proxies = $trusted === '*' ? '*' : array_map('trim', explode(',', $trusted));
        }
    }
}
