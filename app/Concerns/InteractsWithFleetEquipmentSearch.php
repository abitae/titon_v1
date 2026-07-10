<?php

namespace App\Concerns;

use App\Models\FleetEquipment;
use Illuminate\Support\Collection;

trait InteractsWithFleetEquipmentSearch
{
    public string $equipment_search = '';

    public string $filter_equipment_search = '';

    public function selectFleetEquipment(int $equipmentId): void
    {
        $equipment = FleetEquipment::query()->findOrFail($equipmentId);

        $this->fleet_equipment_id = $equipment->id;
        $this->equipment_search = $this->fleetEquipmentSearchLabel($equipment);
    }

    public function selectFilterFleetEquipment(int $equipmentId): void
    {
        $equipment = FleetEquipment::query()->findOrFail($equipmentId);

        $this->filter_equipment_id = $equipment->id;
        $this->filter_equipment_search = $this->fleetEquipmentSearchLabel($equipment);

        if (method_exists($this, 'afterFilterFleetEquipmentChanged')) {
            $this->afterFilterFleetEquipmentChanged();
        }
    }

    public function clearFilterFleetEquipment(): void
    {
        $this->filter_equipment_id = null;
        $this->filter_equipment_search = '';

        if (method_exists($this, 'afterFilterFleetEquipmentChanged')) {
            $this->afterFilterFleetEquipmentChanged();
        }
    }

    public function updatedEquipmentSearch(): void
    {
        $this->clearFleetEquipmentIfSearchMismatch('fleet_equipment_id', 'equipment_search');
    }

    public function updatedFilterEquipmentSearch(): void
    {
        if (blank(trim($this->filter_equipment_search))) {
            $this->filter_equipment_id = null;

            if (method_exists($this, 'afterFilterFleetEquipmentChanged')) {
                $this->afterFilterFleetEquipmentChanged();
            }

            return;
        }

        $this->clearFleetEquipmentIfSearchMismatch('filter_equipment_id', 'filter_equipment_search');
    }

    /**
     * @return Collection<int, array{id: int, label: string, internal_code: string, name: string}>
     */
    protected function fleetEquipmentSelectOptions(?string $search = null, int $limit = 30): Collection
    {
        $term = mb_strtolower(trim($search ?? $this->equipment_search));

        return FleetEquipment::query()
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%'.$term.'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->whereRaw('lower(internal_code) like ?', [$like])
                        ->orWhereRaw('lower(name) like ?', [$like]);
                });
            })
            ->orderBy('internal_code')
            ->limit($limit)
            ->get(['id', 'internal_code', 'name'])
            ->map(fn (FleetEquipment $equipment): array => [
                'id' => $equipment->id,
                'label' => $this->fleetEquipmentSearchLabel($equipment),
                'internal_code' => $equipment->internal_code,
                'name' => $equipment->name,
            ]);
    }

    protected function fleetEquipmentFilterOptions(int $limit = 30): Collection
    {
        return $this->fleetEquipmentSelectOptions($this->filter_equipment_search, $limit);
    }

    protected function syncFleetEquipmentSearch(?int $equipmentId = null): void
    {
        $id = $equipmentId ?? $this->fleet_equipment_id;

        if ($id === null) {
            $this->equipment_search = '';

            return;
        }

        $equipment = FleetEquipment::query()->find($id);
        $this->equipment_search = $equipment instanceof FleetEquipment
            ? $this->fleetEquipmentSearchLabel($equipment)
            : '';
    }

    protected function syncFilterFleetEquipmentSearch(): void
    {
        if ($this->filter_equipment_id === null) {
            $this->filter_equipment_search = '';

            return;
        }

        $equipment = FleetEquipment::query()->find($this->filter_equipment_id);
        $this->filter_equipment_search = $equipment instanceof FleetEquipment
            ? $this->fleetEquipmentSearchLabel($equipment)
            : '';
    }

    protected function fleetEquipmentSearchLabel(FleetEquipment $equipment): string
    {
        return trim($equipment->internal_code.' · '.$equipment->name);
    }

    protected function resetFleetEquipmentSearch(): void
    {
        $this->equipment_search = '';
        $this->filter_equipment_search = '';
    }

    protected function clearFleetEquipmentIfSearchMismatch(string $idProperty, string $searchProperty): void
    {
        $id = $this->{$idProperty};

        if ($id === null) {
            return;
        }

        $equipment = FleetEquipment::query()->find($id);

        if ($equipment === null) {
            $this->{$idProperty} = null;

            return;
        }

        $search = mb_strtolower(trim($this->{$searchProperty}));

        if ($search === '') {
            return;
        }

        $matches = str_contains(mb_strtolower($equipment->internal_code), $search)
            || str_contains(mb_strtolower($equipment->name), $search);

        if (! $matches) {
            $this->{$idProperty} = null;
        }
    }
}
