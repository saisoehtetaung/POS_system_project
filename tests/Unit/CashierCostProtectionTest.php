<?php

namespace Tests\Unit;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Identity\Models\Role;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Purchasing\Models\PurchaseItem;
use App\Domain\Purchasing\Models\SupplierPriceHistory;
use App\Domain\Sales\Models\Sale;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\PurchaseItemResource;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\SupplierPriceHistoryResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CashierCostProtectionTest extends TestCase
{
    public function test_cashier_resources_omit_cost_fields(): void
    {
        $cashier = $this->userWithRole('cashier', false);
        $request = $this->requestAs($cashier);

        $variant = new ProductVariant([
            'sku' => 'PEN-BLU',
            'retail_price' => '150.00',
            'wholesale_price' => '120.00',
            'cost_price' => '80.00',
        ]);
        $variant->id = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

        $movement = new StockMovement([
            'movement_type' => 'sale_out',
            'qty_delta' => '-1.000',
            'qty_after' => '9.000',
            'unit_cost' => '80.00',
        ]);

        $item = new PurchaseItem([
            'quantity' => '1.000',
            'line_total' => '80.00',
            'unit_cost' => '80.00',
        ]);

        $history = new SupplierPriceHistory([
            'unit_cost' => '80.00',
        ]);

        $variantPayload = (new ProductVariantResource($variant))->toArray($request);
        $movementPayload = (new StockMovementResource($movement))->toArray($request);
        $itemPayload = (new PurchaseItemResource($item))->toArray($request);
        $historyPayload = (new SupplierPriceHistoryResource($history))->toArray($request);

        $this->assertArrayNotHasKey('cost_price', $variantPayload);
        $this->assertArrayNotHasKey('unit_cost', $movementPayload);
        $this->assertArrayNotHasKey('unit_cost', $itemPayload);
        $this->assertArrayNotHasKey('unit_cost', $historyPayload);
        $this->assertSame('150.00', $variantPayload['retail_price']);
    }

    public function test_manager_resources_include_cost_fields(): void
    {
        $manager = $this->userWithRole('manager', true);
        $request = $this->requestAs($manager);

        $variant = new ProductVariant([
            'cost_price' => '80.00',
            'retail_price' => '150.00',
        ]);

        $payload = (new ProductVariantResource($variant))->toArray($request);

        $this->assertSame('80.00', $payload['cost_price']);
    }

    public function test_cashier_cannot_void_refund_or_exceed_discount_limit(): void
    {
        $cashier = $this->userWithRole('cashier', false, '5.00', false, false);
        $sale = new Sale();

        $this->assertFalse(Gate::forUser($cashier)->allows('void', $sale));
        $this->assertFalse(Gate::forUser($cashier)->allows('refund', $sale));
        $this->assertTrue(Gate::forUser($cashier)->allows('apply-discount', 5));
        $this->assertFalse(Gate::forUser($cashier)->allows('apply-discount', 5.01));
        $this->assertFalse(Gate::forUser($cashier)->allows('override-protected-action'));
    }

    public function test_manager_can_void_and_override(): void
    {
        $manager = $this->userWithRole('manager', true, '20.00', true, true);
        $sale = new Sale();

        $this->assertTrue(Gate::forUser($manager)->allows('void', $sale));
        $this->assertTrue(Gate::forUser($manager)->allows('refund', $sale));
        $this->assertTrue(Gate::forUser($manager)->allows('override-protected-action'));
        $this->assertTrue(Gate::forUser($manager)->allows('view-cost'));
    }

    private function userWithRole(
        string $name,
        bool $canViewCost,
        string $maxDiscount = '5.00',
        bool $canVoid = false,
        bool $canRefund = false
    ): User {
        $role = new Role();
        $role->forceFill([
            'name' => $name,
            'max_discount_percentage' => $maxDiscount,
            'can_view_cost' => $canViewCost,
            'can_void' => $canVoid,
            'can_refund' => $canRefund,
            'can_adjust_stock' => false,
            'can_manage_purchases' => false,
        ]);

        $user = new User();
        $user->setRelation('role', $role);

        return $user;
    }

    private function requestAs(User $user): Request
    {
        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $request;
    }
}
