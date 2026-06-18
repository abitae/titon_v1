<?php

use App\Actions\Orders\RecordOrderConformity;
use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Actions\Warehouse\RecordWarehouseOutbound;
use App\Actions\Warehouse\TransferWarehouseBetweenProjects;
use App\Enums\ConformityResult;
use App\Enums\OrderStatus;
use App\Enums\WarehouseMovementSource;
use App\Livewire\Warehouse\ManageWarehouse;
use App\Models\Order;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\SupplierQuotationItem;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

function createOrderWithItemsForWarehouse(
    int $companyId,
    Project $project,
    User $user,
    Supplier $supplier,
    string $requirementType = 'material',
    float $quantity = 10,
): Order {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $companyId,
        'work_project_id' => $project->id,
        'requested_by' => $user->id,
        'requirement_type' => $requirementType,
        'code' => 'REQ-WH-'.fake()->unique()->numerify('###'),
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Requerimiento almacen test',
        'status' => 'en_proceso',
    ]);

    $quotation = SupplierQuotation::query()->create([
        'company_id' => $companyId,
        'work_project_id' => $project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $supplier->id,
        'code' => 'COT-WH-'.fake()->unique()->numerify('###'),
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time_days' => 3,
        'status' => 'registrada',
    ]);

    SupplierQuotationItem::query()->create([
        'company_id' => $companyId,
        'work_project_id' => $project->id,
        'supplier_quotation_id' => $quotation->id,
        'product_or_service' => 'Cemento Portland',
        'unit' => 'bol',
        'quantity' => $quantity,
        'unit_price' => 25,
        'total' => 25 * $quantity,
    ]);

    $purchaseRequest->comparison()->create([
        'company_id' => $companyId,
        'work_project_id' => $project->id,
        'selected_supplier_quotation_id' => $quotation->id,
        'selected_by' => $user->id,
        'compared_at' => now(),
        'selection_reason' => 'Mejor precio',
        'order_code' => 'OC-WH-'.fake()->unique()->numerify('###'),
        'order_generated_at' => now(),
    ]);

    $order = app(GeneratePurchaseOrder::class)->handle($purchaseRequest);
    $order->update(['status' => OrderStatus::Attended->value()]);

    return $order->fresh(['items']);
}

test('conform order creates warehouse stock and inbound movement', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create([
        'company_id' => $company->id,
        'responsible_user_id' => $user->id,
    ]);

    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $order = createOrderWithItemsForWarehouse($company->id, $project, $user, $supplier);

    app(RecordOrderConformity::class)->handle(
        $order,
        $user,
        ConformityResult::Conform->value(),
        'Conforme en obra',
    );

    $stockItem = WarehouseStockItem::query()
        ->where('company_id', $company->id)
        ->where('work_project_id', $project->id)
        ->where('description', 'Cemento Portland')
        ->first();

    expect($stockItem)->not->toBeNull();
    expect((float) $stockItem->stock_quantity)->toBe(10.0);
    expect($stockItem->item_type)->toBe('material');

    expect(WarehouseMovement::query()
        ->where('order_id', $order->id)
        ->where('source', WarehouseMovementSource::OrderConformity->value())
        ->count())->toBe(1);
});

test('rejected conformity does not create warehouse movements', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $order = createOrderWithItemsForWarehouse($company->id, $project, $user, $supplier);

    app(RecordOrderConformity::class)->handle(
        $order,
        $user,
        ConformityResult::Rejected->value(),
        'Material defectuoso',
    );

    expect(WarehouseStockItem::query()->where('company_id', $company->id)->count())->toBe(0);
    expect(WarehouseMovement::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('manual outbound reduces stock and blocks insufficient quantity', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $order = createOrderWithItemsForWarehouse($company->id, $project, $user, $supplier, 'material', 5);

    app(RecordOrderConformity::class)->handle($order, $user, ConformityResult::Conform->value());

    $stockItem = WarehouseStockItem::query()->where('description', 'Cemento Portland')->firstOrFail();

    app(RecordWarehouseOutbound::class)->handle($stockItem, $user, [
        'quantity' => '2',
        'movement_date' => now()->toDateString(),
        'reference' => 'Consumo en obra',
    ]);

    expect((float) $stockItem->fresh()->stock_quantity)->toBe(3.0);

    expect(fn () => app(RecordWarehouseOutbound::class)->handle($stockItem->fresh(), $user, [
        'quantity' => '10',
    ]))->toThrow(HttpException::class);
});

test('transfer between projects moves stock atomically', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $sourceProject = Project::factory()->create(['company_id' => $company->id, 'code' => 'OB-A']);
    $destinationProject = Project::factory()->create(['company_id' => $company->id, 'code' => 'OB-B']);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $order = createOrderWithItemsForWarehouse($company->id, $sourceProject, $user, $supplier, 'material', 8);

    app(RecordOrderConformity::class)->handle($order, $user, ConformityResult::Conform->value());

    $sourceItem = WarehouseStockItem::query()
        ->where('work_project_id', $sourceProject->id)
        ->where('description', 'Cemento Portland')
        ->firstOrFail();

    app(TransferWarehouseBetweenProjects::class)->handle($sourceItem, $user, [
        'destination_work_project_id' => $destinationProject->id,
        'quantity' => '3',
        'transfer_date' => now()->toDateString(),
        'reference' => 'Traslado entre obras',
    ]);

    expect((float) $sourceItem->fresh()->stock_quantity)->toBe(5.0);

    $destinationItem = WarehouseStockItem::query()
        ->where('work_project_id', $destinationProject->id)
        ->where('description', 'Cemento Portland')
        ->first();

    expect($destinationItem)->not->toBeNull();
    expect((float) $destinationItem->stock_quantity)->toBe(3.0);

    expect(WarehouseMovement::query()
        ->where('source', WarehouseMovementSource::TransferOutbound->value())
        ->count())->toBe(1);

    expect(WarehouseMovement::query()
        ->where('source', WarehouseMovementSource::TransferInbound->value())
        ->count())->toBe(1);
});

test('transfer blocks same project service items and insufficient stock', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $serviceItem = WarehouseStockItem::factory()->service()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'description' => 'Servicio de instalacion',
        'stock_quantity' => 1,
    ]);

    expect(fn () => app(TransferWarehouseBetweenProjects::class)->handle($serviceItem, $user, [
        'destination_work_project_id' => Project::factory()->create(['company_id' => $company->id])->id,
        'quantity' => '1',
    ]))->toThrow(HttpException::class);

    $materialItem = WarehouseStockItem::factory()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'stock_quantity' => 2,
    ]);

    expect(fn () => app(TransferWarehouseBetweenProjects::class)->handle($materialItem, $user, [
        'destination_work_project_id' => $project->id,
        'quantity' => '1',
    ]))->toThrow(HttpException::class);
});

test('re-registering conformity does not duplicate inbound movements', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create(['company_id' => $company->id]);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);
    $order = createOrderWithItemsForWarehouse($company->id, $project, $user, $supplier, 'material', 4);

    app(RecordOrderConformity::class)->handle($order, $user, ConformityResult::Conform->value(), 'Primera');
    app(RecordOrderConformity::class)->handle($order->fresh(), $user, ConformityResult::Conform->value(), 'Segunda');

    expect(WarehouseMovement::query()
        ->where('order_id', $order->id)
        ->where('source', WarehouseMovementSource::OrderConformity->value())
        ->count())->toBe(1);

    $stockItem = WarehouseStockItem::query()->where('description', 'Cemento Portland')->firstOrFail();
    expect((float) $stockItem->stock_quantity)->toBe(4.0);
});

test('warehouse filters return scoped results', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $projectA = Project::factory()->create(['company_id' => $company->id, 'code' => 'FIL-A']);
    $projectB = Project::factory()->create(['company_id' => $company->id, 'code' => 'FIL-B']);
    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $orderA = createOrderWithItemsForWarehouse($company->id, $projectA, $user, $supplier, 'material', 2);
    $orderB = createOrderWithItemsForWarehouse($company->id, $projectB, $user, $supplier, 'material', 3);

    app(RecordOrderConformity::class)->handle($orderA, $user, ConformityResult::Conform->value());
    app(RecordOrderConformity::class)->handle($orderB, $user, ConformityResult::Conform->value());

    Livewire::test(ManageWarehouse::class)
        ->set('filter_work_project_id', $projectA->id)
        ->assertSee('Cemento Portland')
        ->assertSee('FIL-A');

    Livewire::test(ManageWarehouse::class)
        ->set('activeTab', 'kardex')
        ->set('filter_work_project_id', $projectB->id)
        ->set('filter_responsible_user_id', $user->id)
        ->assertSee('Cemento Portland');
});

test('warehouse page requires permission', function () {
    ['company' => $company, 'user' => $user, 'role' => $role] = authenticateWithCompany('Consulta');

    $role->syncPermissions(['dashboard.ver', 'almacen.ver']);

    Livewire::test(ManageWarehouse::class)->assertSuccessful();

    $role->syncPermissions(['dashboard.ver']);

    Livewire::test(ManageWarehouse::class)->assertForbidden();
});
