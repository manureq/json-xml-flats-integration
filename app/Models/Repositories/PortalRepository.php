<?php

declare(strict_types=1);

namespace App\Models\Repositories;

use App\Support\Database;
use PDO;

final class PortalRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return list<array{id: int, name: string, slug: string, endpoint: ?string, auth_token: ?string, enabled: int}>
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM portals ORDER BY name');
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array{id: int, name: string, slug: string, endpoint: ?string, auth_token: ?string, enabled: int, selected: bool}>
     */
    public function allWithSelections(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT p.*, CASE WHEN pp.portal_id IS NULL THEN 0 ELSE 1 END AS selected
            FROM portals p
            LEFT JOIN property_portal pp ON pp.portal_id = p.id AND pp.property_id = :property
            ORDER BY p.name');
        $stmt->execute([':property' => $propertyId]);
        $items = $stmt->fetchAll() ?: [];
        return array_map(static function (array $item): array {
            $item['selected'] = (bool) $item['selected'];
            return $item;
        }, $items);
    }

    /**
     * @return list<array{id: int, name: string, slug: string}>
     */
    public function forProperty(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT p.* FROM portals p
            INNER JOIN property_portal pp ON pp.portal_id = p.id
            WHERE pp.property_id = :property ORDER BY p.name');
        $stmt->execute([':property' => $propertyId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param list<string> $slugs
     */
    public function sync(int $propertyId, array $slugs): void
    {
        $this->db->prepare('DELETE FROM property_portal WHERE property_id = :property')->execute([':property' => $propertyId]);
        if ($slugs === []) {
            return;
        }
        $stmt = $this->db->prepare('INSERT OR IGNORE INTO property_portal (property_id, portal_id)
            SELECT :property, id FROM portals WHERE slug = :slug');
        foreach ($slugs as $slug) {
            $stmt->execute([
                ':property' => $propertyId,
                ':slug' => $slug,
            ]);
        }
    }

    /**
     * @param list<array{id: int, endpoint: ?string, auth_token: ?string, enabled: int}> $items
     */
    public function updateAll(array $items): void
    {
        $stmt = $this->db->prepare('UPDATE portals SET endpoint = :endpoint, auth_token = :auth_token, enabled = :enabled WHERE id = :id');
        foreach ($items as $item) {
            $stmt->execute([
                ':endpoint' => $item['endpoint'],
                ':auth_token' => $item['auth_token'],
                ':enabled' => $item['enabled'],
                ':id' => $item['id'],
            ]);
        }
    }
}
