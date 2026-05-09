<?php

namespace App\Enums;

enum PlatformModule
{
    case Dashboard;
    case Documents;
    case Projects;
    case Suppliers;
    case Contracts;
    case Payments;
    case Mechanics;

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Documents => 'Documentos',
            self::Projects => 'Obras',
            self::Suppliers => 'Proveedores',
            self::Contracts => 'Contratos',
            self::Payments => 'Pagos',
            self::Mechanics => 'Mecanica',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Dashboard => 'Vista general operativa de la plataforma.',
            self::Documents => 'Repositorio documental multiempresa con trazabilidad.',
            self::Projects => 'Seguimiento operativo y contractual de obras.',
            self::Suppliers => 'Directorio y evaluacion de proveedores.',
            self::Contracts => 'Control de contratos, vigencias y anexos.',
            self::Payments => 'Programacion y control del flujo de pagos.',
            self::Mechanics => 'Maquinaria, revisiones, mantenimientos y repuestos.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Dashboard => 'home',
            self::Documents => 'folder',
            self::Projects => 'building-office-2',
            self::Suppliers => 'users',
            self::Contracts => 'document-text',
            self::Payments => 'banknotes',
            self::Mechanics => 'wrench-screwdriver',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Dashboard => 'dashboard',
            self::Documents => 'modules.documents',
            self::Projects => 'modules.projects',
            self::Suppliers => 'modules.suppliers',
            self::Contracts => 'modules.contracts',
            self::Payments => 'modules.payments',
            self::Mechanics => 'modules.mechanics',
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::Dashboard => 'dashboard',
            self::Documents => 'documents',
            self::Projects => 'projects',
            self::Suppliers => 'suppliers',
            self::Contracts => 'contracts',
            self::Payments => 'payments',
            self::Mechanics => 'mecanica',
        };
    }

    public function isNavCurrent(): bool
    {
        return match ($this) {
            self::Mechanics => request()->routeIs('modules.mechanics') || request()->routeIs('mechanics.*'),
            default => request()->routeIs($this->routeName()),
        };
    }

    /**
     * @return array<int, self>
     */
    public static function businessModules(): array
    {
        return [
            self::Documents,
            self::Projects,
            self::Suppliers,
            self::Contracts,
            self::Payments,
            self::Mechanics,
        ];
    }
}
