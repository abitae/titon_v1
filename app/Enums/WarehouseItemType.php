<?php

namespace App\Enums;

enum WarehouseItemType
{
    case Material;
    case Service;

    public function value(): string
    {
        return match ($this) {
            self::Material => 'material',
            self::Service => 'servicio',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Material => 'Material',
            self::Service => 'Servicio',
        };
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

    public static function fromOrderType(OrderType $orderType): self
    {
        return $orderType === OrderType::Service
            ? self::Service
            : self::Material;
    }
}
