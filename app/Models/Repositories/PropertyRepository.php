<?php

declare(strict_types=1);

namespace App\Models\Repositories;

use App\Models\Property;
use App\Support\Database;
use PDO;

final class PropertyRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paginate(int $perPage = 25): array
    {
        $stmt = $this->db->prepare('SELECT * FROM properties ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM properties WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findIdByExternalId(string $externalId): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM properties WHERE external_id = :external_id LIMIT 1');
        $stmt->execute([':external_id' => $externalId]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    /**
     * @param list<string> $amenities
     * @param list<array{url: string, type: string, caption: string}> $media
     */
    public function store(Property $property, array $amenities, array $media): int
    {
        $stmt = $this->db->prepare('INSERT INTO properties (external_id, title, description, type, address, city, country, latitude, longitude, rent_price, currency, bedrooms, bathrooms, area, available_from, created_at, updated_at)
            VALUES (:external_id, :title, :description, :type, :address, :city, :country, :latitude, :longitude, :rent_price, :currency, :bedrooms, :bathrooms, :area, :available_from, :created_at, :updated_at)');
        $stmt->execute([
            ':external_id' => $property->externalId,
            ':title' => $property->title,
            ':description' => $property->description,
            ':type' => $property->type,
            ':address' => $property->address,
            ':city' => $property->city,
            ':country' => $property->country,
            ':latitude' => $property->latitude,
            ':longitude' => $property->longitude,
            ':rent_price' => $property->rentPrice,
            ':currency' => $property->currency,
            ':bedrooms' => $property->bedrooms,
            ':bathrooms' => $property->bathrooms,
            ':area' => $property->area,
            ':available_from' => $property->availableFrom,
            ':created_at' => $property->createdAt,
            ':updated_at' => $property->updatedAt,
        ]);

        $id = (int) $this->db->lastInsertId();
        $this->syncAmenities($id, $amenities);
        $this->syncMedia($id, $media);
        return $id;
    }

    /**
     * @param list<string> $amenities
     * @param list<array{url: string, type: string, caption: string}> $media
     */
    public function update(int $id, Property $property, array $amenities, array $media): void
    {
        $stmt = $this->db->prepare('UPDATE properties SET external_id = :external_id, title = :title, description = :description, type = :type, address = :address, city = :city, country = :country, latitude = :latitude, longitude = :longitude, rent_price = :rent_price, currency = :currency, bedrooms = :bedrooms, bathrooms = :bathrooms, area = :area, available_from = :available_from, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            ':external_id' => $property->externalId,
            ':title' => $property->title,
            ':description' => $property->description,
            ':type' => $property->type,
            ':address' => $property->address,
            ':city' => $property->city,
            ':country' => $property->country,
            ':latitude' => $property->latitude,
            ':longitude' => $property->longitude,
            ':rent_price' => $property->rentPrice,
            ':currency' => $property->currency,
            ':bedrooms' => $property->bedrooms,
            ':bathrooms' => $property->bathrooms,
            ':area' => $property->area,
            ':available_from' => $property->availableFrom,
            ':updated_at' => date('c'),
            ':id' => $id,
        ]);

        $this->db->prepare('DELETE FROM amenities WHERE property_id = :id')->execute([':id' => $id]);
        $this->db->prepare('DELETE FROM media WHERE property_id = :id')->execute([':id' => $id]);
        $this->syncAmenities($id, $amenities);
        $this->syncMedia($id, $media);
    }

    /**
     * @param list<string> $amenities
     * @param list<array{url: string, type: string, caption: string}> $media
     */
    public function upsert(Property $property, array $amenities, array $media): int
    {
        if ($property->externalId !== null) {
            $existingId = $this->findIdByExternalId($property->externalId);
            if ($existingId !== null) {
                $this->update($existingId, $property, $amenities, $media);
                return $existingId;
            }
        }

        return $this->store($property, $amenities, $media);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM properties WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * @return array{total: int, available: int, avg_price: float|null}
     */
    public function statistics(): array
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM properties')->fetchColumn();
        $available = (int) $this->db->query("SELECT COUNT(*) FROM properties WHERE date(available_from) <= date('now') OR available_from IS NULL")
            ->fetchColumn();
        $avg = $this->db->query('SELECT AVG(rent_price) FROM properties WHERE rent_price IS NOT NULL')->fetchColumn();

        return [
            'total' => $total,
            'available' => $available,
            'avg_price' => $avg !== false ? (float) $avg : null,
        ];
    }

    /**
     * @param list<string> $amenities
     */
    private function syncAmenities(int $propertyId, array $amenities): void
    {
        $stmt = $this->db->prepare('INSERT INTO amenities (property_id, name) VALUES (:property_id, :name)');
        foreach ($amenities as $name) {
            $stmt->execute([
                ':property_id' => $propertyId,
                ':name' => $name,
            ]);
        }
    }

    /**
     * @param list<array{url: string, type: string, caption: string}> $media
     */
    private function syncMedia(int $propertyId, array $media): void
    {
        $stmt = $this->db->prepare('INSERT INTO media (property_id, url, type, caption) VALUES (:property_id, :url, :type, :caption)');
        foreach ($media as $item) {
            $stmt->execute([
                ':property_id' => $propertyId,
                ':url' => $item['url'],
                ':type' => $item['type'],
                ':caption' => $item['caption'],
            ]);
        }
    }
}
