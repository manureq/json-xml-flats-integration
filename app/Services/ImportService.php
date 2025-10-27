<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Property;
use App\Models\Repositories\PropertyRepository;
use SimpleXMLElement;

final class ImportService
{
    public function __construct(private readonly PropertyRepository $properties = new PropertyRepository())
    {
    }

    public function import(string $path): int
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("El archivo {$path} no existe");
        }

        $contents = (string) file_get_contents($path);
        return $this->importFromString($contents);
    }

    public function importFromString(string $xmlContent): int
    {
        $xmlContent = trim($xmlContent);
        if ($xmlContent === '') {
            throw new \InvalidArgumentException('El XML proporcionado está vacío.');
        }

        $xml = new SimpleXMLElement($xmlContent);
        return $this->ingest($xml);
    }

    private function ingest(SimpleXMLElement $xml): int
    {
        $count = 0;
        foreach ($xml->property as $node) {
            $data = $this->mapNode($node);
            $property = Property::fromArray($data);
            $this->properties->upsert($property, $data['amenities'], $data['media']);
            $count++;
        }

        return $count;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapNode(SimpleXMLElement $node): array
    {
        $data = [
            'external_id' => $this->string($node->id ?? ''),
            'title' => $this->string($node->title ?? ''),
            'description' => $this->string($node->description ?? ''),
            'type' => $this->string($node->type ?? ''),
            'address' => $this->string($node->address ?? ''),
            'city' => $this->string($node->city ?? ''),
            'country' => $this->string($node->country ?? ''),
            'latitude' => $this->float($node->latitude ?? null),
            'longitude' => $this->float($node->longitude ?? null),
            'rent_price' => $this->float($node->rent_price ?? null),
            'currency' => $this->string($node->currency ?? ''),
            'bedrooms' => $this->int($node->bedrooms ?? null),
            'bathrooms' => $this->int($node->bathrooms ?? null),
            'area' => $this->float($node->area ?? null),
            'available_from' => $this->string($node->available_from ?? ''),
            'amenities' => [],
            'media' => [],
        ];

        if (isset($node->amenities->amenity)) {
            foreach ($node->amenities->amenity as $amenity) {
                $value = $this->string($amenity);
                if ($value !== '') {
                    $data['amenities'][] = $value;
                }
            }
        }

        if (isset($node->media_items->media)) {
            foreach ($node->media_items->media as $media) {
                $url = $this->string($media->url ?? '');
                if ($url === '') {
                    continue;
                }
                $data['media'][] = [
                    'url' => $url,
                    'type' => $this->string($media->type ?? 'image'),
                    'caption' => $this->string($media->caption ?? ''),
                ];
            }
        }

        return $data;
    }

    private function string(mixed $value): string
    {
        return trim((string) $value);
    }

    private function float(mixed $value): ?float
    {
        $value = trim((string) $value);
        return $value === '' ? null : (float) $value;
    }

    private function int(mixed $value): ?int
    {
        $value = trim((string) $value);
        return $value === '' ? null : (int) $value;
    }
}
