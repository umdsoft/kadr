<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Bazaviy controller.
 *
 * Illuminate\Routing\Controller'dan meros — controller-level middleware
 * ($this->middleware(...)) va shu orqali authorizeResource() ishlashi uchun.
 * Laravel 11+ standart bo'sh Controller'ida bu mexanizm yo'q edi.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
