<?php

declare(strict_types=1);

namespace App\Models;

final class Media
{
    public function __construct(
        public int $id,
        public int $propertyId,
        public string $url,
        public string $type,
        public ?string $caption,
    ) {
    }
}
