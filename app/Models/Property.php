<?php

declare(strict_types=1);

namespace App\Models;

final class Property
{
    public function __construct(
        public ?int $id,
        public ?string $externalId,
        public string $title,
        public string $description,
        public string $type,
        public string $address,
        public string $city,
        public string $country,
        public ?float $latitude,
        public ?float $longitude,
        public ?float $rentPrice,
        public string $currency,
        public ?int $bedrooms,
        public ?int $bathrooms,
        public ?float $area,
        public ?string $availableFrom,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $now = date('c');
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            externalId: $data['external_id'] ?? null,
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            address: (string) ($data['address'] ?? ''),
            city: (string) ($data['city'] ?? ''),
            country: (string) ($data['country'] ?? ''),
            latitude: $data['latitude'] !== null ? (float) $data['latitude'] : null,
            longitude: $data['longitude'] !== null ? (float) $data['longitude'] : null,
            rentPrice: $data['rent_price'] !== null ? (float) $data['rent_price'] : null,
            currency: (string) ($data['currency'] ?? ''),
            bedrooms: $data['bedrooms'] !== null ? (int) $data['bedrooms'] : null,
            bathrooms: $data['bathrooms'] !== null ? (int) $data['bathrooms'] : null,
            area: $data['area'] !== null ? (float) $data['area'] : null,
            availableFrom: $data['available_from'] ?? null,
            createdAt: $data['created_at'] ?? $now,
            updatedAt: $data['updated_at'] ?? $now,
        );
    }
}
