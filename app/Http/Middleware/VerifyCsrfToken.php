<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Exempt API admin/session endpoints so SPA can use cookie-based sessions
        'api/admin/login',
        'api/admin/logout',
        'api/portfolio',
        'api/portfolio/*',
        'admin/login',
        'admin/logout',
        'portfolio',
        'portfolio/*',
        'contact', // Contact form endpoint
    ];
}
