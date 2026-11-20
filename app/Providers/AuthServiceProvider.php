<?php

namespace App\Providers;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Sales\Models\Sale;
use App\Models\User;
use App\Policies\ProductVariantPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\SalePolicy;
use App\Policies\StockMovementPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Sale::class => SalePolicy::class,
        Purchase::class => PurchasePolicy::class,
        StockMovement::class => StockMovementPolicy::class,
        ProductVariant::class => ProductVariantPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-cost', function (User $user) {
            return $user->canViewCost();
        });

        Gate::define('apply-discount', function (User $user, $percent) {
            $user->loadMissing('role');

            if (! $user->role) {
                return false;
            }

            $requested = (int) round(((float) $percent) * 100);
            $allowed = (int) round(((float) $user->role->max_discount_percentage) * 100);

            return $requested <= $allowed;
        });

        Gate::define('override-protected-action', function (User $user) {
            return $user->hasRole('owner', 'manager');
        });
    }
}
