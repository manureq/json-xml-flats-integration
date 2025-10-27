<?php

declare(strict_types=1);

namespace App\Models;

final class Amenity
{
    public function __construct(
        public int $id,
        public int $propertyId,
        public string $name,
    ) {
    }
}
