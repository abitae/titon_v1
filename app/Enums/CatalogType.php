<?php

namespace App\Enums;

enum CatalogType
{
    case City;
    case Area;
    case Bank;
    case PaymentMethod;
    case OperationType;
    case DocumentType;
    case DocumentStatus;
    case EquipmentType;

    public function value(): string
    {
        return match ($this) {
            self::City => 'cities',
            self::Area => 'areas',
            self::Bank => 'banks',
            self::PaymentMethod => 'payment_methods',
            self::OperationType => 'operation_types',
            self::DocumentType => 'document_types',
            self::DocumentStatus => 'document_statuses',
            self::EquipmentType => 'equipment_types',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::City => 'Ciudades',
            self::Area => 'Areas',
            self::Bank => 'Bancos',
            self::PaymentMethod => 'Metodos de pago',
            self::OperationType => 'Tipos de operacion',
            self::DocumentType => 'Tipos de documento',
            self::DocumentStatus => 'Estados documentarios',
            self::EquipmentType => 'Tipos de equipo',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::City, self::Area => 'general',
            self::Bank, self::PaymentMethod, self::OperationType => 'finance',
            self::DocumentType, self::DocumentStatus => 'documents',
            self::EquipmentType => 'mechanics',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function groups(): array
    {
        return [
            'general' => 'General',
            'finance' => 'Finanzas y pagos',
            'documents' => 'Documentos',
            'mechanics' => 'Mecanica',
        ];
    }

    /**
     * @return list<self>
     */
    public static function forGroup(string $group): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $type): bool => $type->group() === $group,
        ));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $type): string => $type->value(),
            self::cases(),
        );
    }

    public static function fromValue(string $value): self
    {
        return collect(self::cases())
            ->firstOrFail(fn (self $type): bool => $type->value() === $value);
    }
}
