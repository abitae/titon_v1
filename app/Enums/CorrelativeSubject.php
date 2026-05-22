<?php

namespace App\Enums;

/**
 * Ámbitos de numeración correlativa por empresa (plantilla + secuencia por año y serie).
 */
enum CorrelativeSubject: string
{
    case Project = 'project';
    case Document = 'document';
    case Requirement = 'requirement';
    case SupplierQuotation = 'supplier_quotation';
    case QuotationComparison = 'quotation_comparison';
    case Order = 'order';
    case AccountsPayable = 'accounts_payable';
    case AccountsPayablePayment = 'accounts_payable_payment';
    /** @deprecated Use Requirement */
    case PurchaseRequest = 'purchase_request';
    /** @deprecated Use Order */
    case PurchaseOrder = 'purchase_order';
    case SupplierContract = 'supplier_contract';
    case ContractPaymentSchedule = 'contract_payment_schedule';
    case SupplierPayment = 'supplier_payment';
    case FleetEquipment = 'fleet_equipment';
    case FleetWorkOrder = 'fleet_work_order';
    case FleetTechnicalInspection = 'fleet_technical_inspection';
    case FleetPreventiveMaintenance = 'fleet_preventive_maintenance';
    case FleetCorrectiveMaintenance = 'fleet_corrective_maintenance';
    case FleetSparePart = 'fleet_spare_part';
    case FleetSparePartMovement = 'fleet_spare_part_movement';
    case ExportedReport = 'exported_report';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Obras',
            self::Document => 'Documentos',
            self::Requirement, self::PurchaseRequest => 'Requerimientos',
            self::SupplierQuotation => 'Cotizaciones de proveedor',
            self::QuotationComparison => 'Comparativas de cotizaciones',
            self::Order, self::PurchaseOrder => 'Órdenes',
            self::AccountsPayable => 'Cuentas por pagar',
            self::AccountsPayablePayment => 'Pagos CxP',
            self::SupplierContract => 'Contratos con proveedor',
            self::ContractPaymentSchedule => 'Cronograma de pagos (cuotas)',
            self::SupplierPayment => 'Pagos a proveedor',
            self::FleetEquipment => 'Equipos y maquinarias',
            self::FleetWorkOrder => 'Órdenes de trabajo mecánica',
            self::FleetTechnicalInspection => 'Revisiones técnicas',
            self::FleetPreventiveMaintenance => 'Mantenimientos preventivos',
            self::FleetCorrectiveMaintenance => 'Mantenimientos correctivos',
            self::FleetSparePart => 'Repuestos (catálogo)',
            self::FleetSparePartMovement => 'Movimientos de inventario (kardex)',
            self::ExportedReport => 'Reportes exportados',
        };
    }

    /**
     * @return array{suffix: string, template: string, pad_length: int}
     */
    public function defaultFormat(): array
    {
        return match ($this) {
            self::Project => ['suffix' => 'OB', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::Document => ['suffix' => 'DOC', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::Requirement, self::PurchaseRequest => ['suffix' => 'REQ', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::SupplierQuotation => ['suffix' => 'COT', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::QuotationComparison => ['suffix' => 'COMP', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::Order, self::PurchaseOrder => ['suffix' => 'OC', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::AccountsPayable => ['suffix' => 'CXP', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::AccountsPayablePayment => ['suffix' => 'PAG', 'template' => '{prefix}-{series}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::SupplierContract => ['suffix' => 'CON', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::ContractPaymentSchedule => ['suffix' => 'CRON', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::SupplierPayment => ['suffix' => 'PAG', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetEquipment => ['suffix' => 'EQ', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetWorkOrder => ['suffix' => 'OT', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetTechnicalInspection => ['suffix' => 'REV', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetPreventiveMaintenance => ['suffix' => 'MP', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetCorrectiveMaintenance => ['suffix' => 'MC', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetSparePart => ['suffix' => 'REP', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::FleetSparePartMovement => ['suffix' => 'MOV', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
            self::ExportedReport => ['suffix' => 'RPT', 'template' => '{prefix}-{suffix}-{year}-{number}', 'pad_length' => 6],
        };
    }
}
