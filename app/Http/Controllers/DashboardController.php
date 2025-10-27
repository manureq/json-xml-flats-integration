<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Repositories\PropertyRepository;
use App\Support\Auth;
use App\Support\View;

final class DashboardController
{
    private PropertyRepository $properties;

    public function __construct()
    {
        Auth::requireLogin();
        $this->properties = new PropertyRepository();
    }

    public function index(): void
    {
        $stats = $this->properties->statistics();
        echo View::render('dashboard', [
            'stats' => $stats,
        ]);
    }
}
