<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FeedService;

final class FeedController
{
    private FeedService $feeds;

    public function __construct()
    {
        $this->feeds = new FeedService();
    }

    public function json(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo $this->feeds->asJson();
    }

    public function xml(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->feeds->asXml();
    }
}
