<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Repositories\SettingRepository;
use App\Support\Database;
use DOMDocument;
use PDO;
use function htmlspecialchars;

final class FeedService
{
    private PDO $db;
    private SettingRepository $settings;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->settings = new SettingRepository();
    }

    public function asJson(): string
    {
        $properties = $this->allProperties();
        $settings = $this->settings->all();
        $meta = [
            'site' => $settings['site_name'] ?? 'Inmobiliaria Segura',
            'contact_email' => $settings['contact_email'] ?? null,
            'support_phone' => $settings['support_phone'] ?? null,
            'default_currency' => $settings['default_currency'] ?? null,
            'generated_at' => date('c'),
        ];
        if (!empty($settings['feed_note'])) {
            $meta['note'] = $settings['feed_note'];
        }

        $meta = array_filter($meta, static fn ($value) => $value !== null && $value !== '');

        return json_encode([
            'meta' => $meta,
            'properties' => $properties,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function asXml(): string
    {
        $properties = $this->allProperties();
        $settings = $this->settings->all();

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('properties');

        $metaNode = $doc->createElement('meta');
        $metaNode->appendChild($doc->createElement('site', $this->escape($settings['site_name'] ?? 'Inmobiliaria Segura')));
        if (!empty($settings['contact_email'])) {
            $metaNode->appendChild($doc->createElement('contact_email', $this->escape($settings['contact_email'])));
        }
        if (!empty($settings['support_phone'])) {
            $metaNode->appendChild($doc->createElement('support_phone', $this->escape($settings['support_phone'])));
        }
        if (!empty($settings['default_currency'])) {
            $metaNode->appendChild($doc->createElement('default_currency', $this->escape($settings['default_currency'])));
        }
        if (!empty($settings['feed_note'])) {
            $metaNode->appendChild($doc->createElement('note', $this->escape($settings['feed_note'])));
        }
        $metaNode->appendChild($doc->createElement('generated_at', date('c')));
        $root->appendChild($metaNode);

        foreach ($properties as $property) {
            $item = $doc->createElement('property');
            foreach ($property as $key => $value) {
                if (in_array($key, ['amenities', 'media', 'portals'], true)) {
                    continue;
                }
                $item->appendChild($doc->createElement($key, $this->escape((string) ($value ?? ''))));
            }

            $amenitiesNode = $doc->createElement('amenities');
            foreach ($property['amenities'] as $amenity) {
                $amenitiesNode->appendChild($doc->createElement('amenity', $this->escape($amenity)));
            }
            $item->appendChild($amenitiesNode);

            $mediaNode = $doc->createElement('media_items');
            foreach ($property['media'] as $media) {
                $mediaItem = $doc->createElement('media');
                $mediaItem->appendChild($doc->createElement('url', $this->escape($media['url'])));
                $mediaItem->appendChild($doc->createElement('type', $this->escape($media['type'])));
                if ($media['caption'] !== null) {
                    $mediaItem->appendChild($doc->createElement('caption', $this->escape((string) $media['caption'])));
                }
                $mediaNode->appendChild($mediaItem);
            }
            $item->appendChild($mediaNode);

            $portalsNode = $doc->createElement('portals');
            foreach ($property['portals'] as $portal) {
                $portalNode = $doc->createElement('portal');
                $portalNode->appendChild($doc->createElement('name', $this->escape($portal['name'])));
                $portalNode->appendChild($doc->createElement('slug', $this->escape($portal['slug'])));
                $portalsNode->appendChild($portalNode);
            }
            $item->appendChild($portalsNode);

            $root->appendChild($item);
        }

        $doc->appendChild($root);
        return (string) $doc->saveXML();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allProperties(): array
    {
        $stmt = $this->db->query('SELECT * FROM properties ORDER BY created_at DESC');
        $properties = $stmt->fetchAll() ?: [];
        foreach ($properties as &$property) {
            $property['amenities'] = $this->fetchColumn('SELECT name FROM amenities WHERE property_id = :id', $property['id']);
            $property['media'] = $this->fetchMedia((int) $property['id']);
            $property['portals'] = $this->fetchPortals((int) $property['id']);
        }
        unset($property);

        return $properties;
    }

    /**
     * @return list<string>
     */
    private function fetchColumn(string $sql, int $propertyId): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $propertyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $rows !== false ? array_map('strval', $rows) : [];
    }

    /**
     * @return list<array{url: string, type: string, caption: ?string}>
     */
    private function fetchMedia(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT url, type, caption FROM media WHERE property_id = :id');
        $stmt->execute([':id' => $propertyId]);
        $rows = $stmt->fetchAll() ?: [];
        return array_map(static function (array $row): array {
            return [
                'url' => (string) $row['url'],
                'type' => (string) $row['type'],
                'caption' => $row['caption'] !== null ? (string) $row['caption'] : null,
            ];
        }, $rows);
    }

    /**
     * @return list<array{name: string, slug: string}>
     */

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function fetchPortals(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT p.name, p.slug FROM portals p INNER JOIN property_portal pp ON pp.portal_id = p.id WHERE pp.property_id = :id');
        $stmt->execute([':id' => $propertyId]);
        $rows = $stmt->fetchAll() ?: [];
        return array_map(static fn (array $row): array => ['name' => (string) $row['name'], 'slug' => (string) $row['slug']], $rows);
    }
}
