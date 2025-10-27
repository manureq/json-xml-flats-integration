<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Repositories\AmenityRepository;
use App\Models\Repositories\MediaRepository;
use App\Models\Repositories\PortalRepository;
use App\Models\Repositories\PropertyRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\View;

final class PropertyController
{
    private PropertyRepository $properties;
    private AmenityRepository $amenities;
    private MediaRepository $media;
    private PortalRepository $portals;

    public function __construct()
    {
        Auth::requireLogin();
        $this->properties = new PropertyRepository();
        $this->amenities = new AmenityRepository();
        $this->media = new MediaRepository();
        $this->portals = new PortalRepository();
    }

    public function index(): void
    {
        $items = $this->properties->paginate();
        echo View::render('properties/index', [
            'properties' => $items,
        ]);
    }

    public function create(): void
    {
        echo View::render('properties/form', [
            'property' => null,
            'csrf' => Csrf::token(),
            'portals' => $this->portals->all(),
        ]);
    }

    public function store(): void
    {
        $payload = $this->sanitize($_POST);
        $property = Property::fromArray($payload);
        $id = $this->properties->store($property, $payload['amenities'] ?? [], $payload['media'] ?? []);
        $this->portals->sync($id, $payload['portals'] ?? []);
        flash('Propiedad creada correctamente.');
        redirect('/properties');
    }

    public function showByQuery(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $property = $this->properties->find($id);
        if (!$property) {
            http_response_code(404);
            echo 'Property not found';
            return;
        }

        echo View::render('properties/show', [
            'property' => $property,
            'amenities' => $this->amenities->forProperty($id),
            'media' => $this->media->forProperty($id),
            'portals' => $this->portals->forProperty($id),
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $property = $this->properties->find($id);
        if (!$property) {
            http_response_code(404);
            echo 'Property not found';
            return;
        }

        echo View::render('properties/form', [
            'property' => $property,
            'amenities' => $this->amenities->forProperty($id),
            'media' => $this->media->forProperty($id),
            'csrf' => Csrf::token(),
            'portals' => $this->portals->allWithSelections($id),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->sanitize($_POST);
        $property = Property::fromArray($payload);
        $this->properties->update($id, $property, $payload['amenities'] ?? [], $payload['media'] ?? []);
        $this->portals->sync($id, $payload['portals'] ?? []);
        flash('Propiedad actualizada correctamente.');
        redirect('/properties');
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $this->properties->delete($id);
        flash('Propiedad eliminada.');
        redirect('/properties');
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function sanitize(array $input): array
    {
        $clean = [];
        $clean['external_id'] = trim((string) ($input['external_id'] ?? '')) ?: null;
        $clean['title'] = trim((string) ($input['title'] ?? ''));
        $clean['description'] = trim((string) ($input['description'] ?? ''));
        $clean['type'] = trim((string) ($input['type'] ?? ''));
        $clean['address'] = trim((string) ($input['address'] ?? ''));
        $clean['city'] = trim((string) ($input['city'] ?? ''));
        $clean['country'] = trim((string) ($input['country'] ?? ''));
        $clean['latitude'] = $input['latitude'] !== '' ? (float) $input['latitude'] : null;
        $clean['longitude'] = $input['longitude'] !== '' ? (float) $input['longitude'] : null;
        $clean['rent_price'] = $input['rent_price'] !== '' ? (float) $input['rent_price'] : null;
        $clean['currency'] = trim((string) ($input['currency'] ?? ''));
        $clean['bedrooms'] = $input['bedrooms'] !== '' ? (int) $input['bedrooms'] : null;
        $clean['bathrooms'] = $input['bathrooms'] !== '' ? (int) $input['bathrooms'] : null;
        $clean['area'] = $input['area'] !== '' ? (float) $input['area'] : null;
        $clean['available_from'] = trim((string) ($input['available_from'] ?? ''));

        $clean['amenities'] = array_filter(array_map('trim', $input['amenities'] ?? []));

        $clean['media'] = [];
        foreach ($input['media_url'] ?? [] as $index => $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            $type = trim((string) ($input['media_type'][$index] ?? 'image'));
            $caption = trim((string) ($input['media_caption'][$index] ?? ''));
            $clean['media'][] = ['url' => $url, 'type' => $type, 'caption' => $caption];
        }

        $clean['portals'] = array_map('trim', $input['portals'] ?? []);

        return $clean;
    }
}
