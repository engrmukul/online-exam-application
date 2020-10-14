<?php

namespace App\Providers;

use App\Contracts\CategoryContract;
use App\Contracts\CustomerContract;
use App\Contracts\ExpenseContract;
use App\Contracts\ExpenseTypeContract;
use App\Contracts\PayableContract;
use App\Contracts\ProductContract;
use App\Contracts\PurchaseContract;
use App\Contracts\PurchaseInvoiceContract;
use App\Contracts\ReceivableContract;
use App\Contracts\ReportContract;
use App\Contracts\ReturnPurchaseContract;
use App\Contracts\ReturnPurchaseInvoiceContract;
use App\Contracts\ReturnSaleContract;
use App\Contracts\ReturnSaleInvoiceContract;
use App\Contracts\SaleContract;
use App\Contracts\SaleInvoiceContract;
use App\Contracts\StaffContract;
use App\Contracts\StockContract;
use App\Contracts\UnitContract;
use App\Contracts\UserContract;
use App\Contracts\VendorContract;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\ExpenseTypeRepository;
use App\Repositories\PayableRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseInvoiceRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ReceivableRepository;
use App\Repositories\ReturnPurchaseInvoiceRepository;
use App\Repositories\ReturnPurchaseRepository;
use App\Repositories\ReturnSaleInvoiceRepository;
use App\Repositories\ReturnSaleRepository;
use App\Repositories\SaleInvoiceRepository;
use App\Repositories\SaleRepository;
use App\Repositories\StaffRepository;
use App\Repositories\StockRepository;
use App\Repositories\UnitRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;
use App\Contracts\ShopContract;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use App\Repositories\ReportRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $repositories = [
        UserContract::class => UserRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
