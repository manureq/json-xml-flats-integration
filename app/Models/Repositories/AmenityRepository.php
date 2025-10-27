<?php

declare(strict_types=1);

namespace App\Models\Repositories;

use App\Support\Database;
use PDO;

final class AmenityRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return list<array{id: int, property_id: int, name: string}>
     */
    public function forProperty(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM amenities WHERE property_id = :id ORDER BY name');
        $stmt->execute([':id' => $propertyId]);
        return $stmt->fetchAll() ?: [];
    }
}
