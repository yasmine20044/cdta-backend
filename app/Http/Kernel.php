<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // Middleware global pour toutes les requêtes
    protected $middleware = [
        \App\Http\Middleware\SecureHeaders::class, // pour ajouter les headers sécurité
    ];
}