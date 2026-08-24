<?php
namespace App\Providers;

use App\Models\AccountLedger;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\Dispatch;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Note;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrapThree();

        foreach ($this->tenantModels() as $model) {
            $model::addGlobalScope('authenticated_company', function (Builder $builder): void {
                if (!app()->bound('request')) {
                    return;
                }

                $companyId = request()->attributes->get('company_id');

                if ($companyId) {
                    $builder->where(
                        $builder->getModel()->qualifyColumn('company_id'),
                        $companyId
                    );
                }
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Laravel\Passport\Console\InstallCommand::class,
                \Laravel\Passport\Console\KeysCommand::class,
                \Laravel\Passport\Console\ClientCommand::class,
            ]);
        }
    }

    private function tenantModels(): array
    {
        return [
            AccountLedger::class,
            Address::class,
            AuditLog::class,
            CompanySetting::class,
            Dispatch::class,
            Estimate::class,
            EstimateItem::class,
            Expense::class,
            ExpenseCategory::class,
            Inventory::class,
            Invoice::class,
            InvoiceItem::class,
            Item::class,
            Note::class,
            OrderItems::class,
            Orders::class,
            Payment::class,
            Receipt::class,
            User::class,
            Voucher::class,
        ];
    }
}
