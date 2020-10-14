<?php

namespace App\Repositories;

use App\Contracts\ReportContract;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Report;
use App\Models\Sale;
use App\Models\Staff;
use App\Models\Stock;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class ReportRepository extends BaseRepository implements ReportContract
{
    /**
     * ProductRepository constructor.
     * @param Report $model
     */
    public function __construct(Report $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    /**
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return mixed
     */
    public function productReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Product::query('units', 'categories');

            if (isset($collection['category_id']) && !empty($collection['category_id'])) {
                $query->where('category_id', $collection['category_id']);
            }

            /*if (isset($collection['selectType']) && !empty($collection['selectType'])) {
                $query->where('category_id', $collection['category_id']);
            }*/

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function purchaseReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Purchase::query()->with('purchaseInvoices', 'purchaseProducts', 'vendor');

            if (isset($collection['vendor_id']) && !empty($collection['vendor_id'])) {
                $query->where('vendor_id', $collection['vendor_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('purchase_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('purchase_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('purchase_date', [$collection['start_date'], $collection['end_date']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function salesReport(array $params)
    {
        try {
            $startWeek = strtotime("last saturday midnight");
            $endWeek = strtotime("-1 week",$startWeek);

            $startWeek = date("Y-m-d",$startWeek);
            $endWeek = date("Y-m-d",$endWeek);

            $today = Carbon::today();
            $lastDay = Carbon::yesterday();
            $lastWeek = [$startWeek, $endWeek];
            $lastMonth =  Carbon::now()->subMonth()->format('m');
            $thisMonth = Carbon::now()->format('m');
            $thisYear = Carbon::now()->format('Y');
            $lastYear =  date("Y",strtotime("-1 year"));

            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Sale::query()->with('customer');

            if (isset($collection['customer_id']) && !empty($collection['customer_id'])) {
                $query->where('customer_id', $collection['customer_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('sale_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('sale_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('sale_date', [$collection['start_date'], $collection['end_date']]);
            }

            if (!empty($collection['timePeriod']) && !empty($collection['timePeriod'])) {

                switch ($collection['timePeriod']) {
                    case 'today':
                        $query->whereDate('sale_date', '=', $today);
                        break;
                    case 'lastDay':
                        $query->whereDate('sale_date', '=', $lastDay);
                        break;
                    case 'lastWeek':
                        $query->whereBetween('sale_date', $lastWeek);
                        break;
                    case 'lastMonth':
                        $query->whereMonth('sale_date', '=', $lastMonth);
                        break;
                    case 'thisMonth':
                        $query->whereMonth('sale_date', '=', $thisMonth);
                        break;
                    case 'thisYear':
                        $query->whereYear('sale_date', $thisYear);
                        break;
                    case 'lastYear':
                        $query->whereYear('sale_date', $lastYear);
                        break;
                    default:
                        $query->where('sale_date', $today);
                }
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    public function stockReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Stock::query()->with('product');

            if (isset($collection['product_id']) && !empty($collection['product_id'])) {
                $query->where('product_id', $collection['product_id']);
            }

            $query->select("*")
                ->selectRaw("SUM(quantity) as total_quantity")
                ->groupBy('product_id');

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    public function totalSales($params){
        try {
            $startWeek = strtotime("last saturday midnight");
            $endWeek = strtotime("-1 week",$startWeek);

            $startWeek = date("Y-m-d",$startWeek);
            $endWeek = date("Y-m-d",$endWeek);

            $today = Carbon::today();
            $lastDay = Carbon::yesterday();
            $lastWeek = [$startWeek, $endWeek];
            $lastMonth =  Carbon::now()->subMonth()->format('m');
            $thisMonth = Carbon::now()->format('m');
            $thisYear = Carbon::now()->format('Y');
            $lastYear =  date("Y",strtotime("-1 year"));

            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Sale::query()->with('salesInvoices','salesProducts');

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('sale_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('sale_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('sale_date', [$collection['start_date'], $collection['end_date']]);
            }

            if (!empty($collection['timePeriod']) && !empty($collection['timePeriod'])) {

                switch ($collection['timePeriod']) {
                    case 'today':
                        $query->whereDate('sale_date', '=', $today);
                        break;
                    case 'lastDay':
                        $query->whereDate('sale_date', '=', $lastDay);
                        break;
                    case 'lastWeek':
                        $query->whereBetween('sale_date', $lastWeek);
                        break;
                    case 'lastMonth':
                        $query->whereMonth('sale_date', '=', $lastMonth);
                        break;
                    case 'thisMonth':
                        $query->whereMonth('sale_date', '=', $thisMonth);
                        break;
                    case 'thisYear':
                        $query->whereYear('sale_date', $thisYear);
                        break;
                    case 'lastYear':
                        $query->whereYear('sale_date', $lastYear);
                        break;
                    default:
                        $query->where('sale_date', $today);
                }
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    public function expenses($params){
        try {
            $startWeek = strtotime("last saturday midnight");
            $endWeek = strtotime("-1 week",$startWeek);

            $startWeek = date("Y-m-d",$startWeek);
            $endWeek = date("Y-m-d",$endWeek);

            $today = Carbon::today();
            $lastDay = Carbon::yesterday();
            $lastWeek = [$startWeek, $endWeek];
            $lastMonth =  Carbon::now()->subMonth()->format('m');
            $thisMonth = Carbon::now()->format('m');
            $thisYear = Carbon::now()->format('Y');
            $lastYear =  date("Y",strtotime("-1 year"));

            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Expense::query()->with('expenseType');

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('expense_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('expense_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('expense_date', [$collection['start_date'], $collection['end_date']]);
            }

            if (!empty($collection['timePeriod']) && !empty($collection['timePeriod'])) {

                switch ($collection['timePeriod']) {
                    case 'today':
                        $query->whereDate('expense_date', '=', $today);
                        break;
                    case 'lastDay':
                        $query->whereDate('expense_date', '=', $lastDay);
                        break;
                    case 'lastWeek':
                        $query->whereBetween('expense_date', $lastWeek);
                        break;
                    case 'lastMonth':
                        $query->whereMonth('expense_date', '=', $lastMonth);
                        break;
                    case 'thisMonth':
                        $query->whereMonth('expense_date', '=', $thisMonth);
                        break;
                    case 'thisYear':
                        $query->whereYear('expense_date', $thisYear);
                        break;
                    case 'lastYear':
                        $query->whereYear('expense_date', $lastYear);
                        break;
                    default:
                        $query->where('expense_date', $today);
                }
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    public function profitLossReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Purchase::query()->with('purchaseInvoices', 'purchaseProducts', 'vendor');

            if (isset($collection['vendor_id']) && !empty($collection['vendor_id'])) {
                $query->where('vendor_id', $collection['vendor_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('purchase_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('purchase_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('purchase_date', [$collection['start_date'], $collection['end_date']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function customerReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Customer::query();

            if (isset($collection['name']) && !empty($collection['name'])) {
                $query->where('name', 'like', '%' . $collection['name'] . '%');
            }

            if (isset($collection['mobile']) && !empty($collection['mobile'])) {
                $query->where('mobile', $collection['mobile']);
            }

            if (isset($collection['start_balance']) && !empty($collection['start_balance'])) {
                $query->where('balance', '>=', $collection['start_balance']);
            }

            if (isset($collection['end_balance']) && !empty($collection['end_balance'])) {
                $query->where('balance', '<=', $collection['end_balance']);
            }

            if (!empty($collection['start_balance']) && !empty($collection['end_balance'])) {
                $query->whereBetween('balance', [$collection['start_balance'], $collection['end_balance']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function vendorReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Vendor::query();

            if (isset($collection['name']) && !empty($collection['name'])) {
                $query->where('name', 'like', '%' . $collection['name'] . '%');
            }

            if (isset($collection['mobile']) && !empty($collection['mobile'])) {
                $query->where('mobile', $collection['mobile']);
            }

            if (isset($collection['start_balance']) && !empty($collection['start_balance'])) {
                $query->where('balance', '>=', $collection['start_balance']);
            }

            if (isset($collection['end_balance']) && !empty($collection['end_balance'])) {
                $query->where('balance', '<=', $collection['end_balance']);
            }

            if (!empty($collection['start_balance']) && !empty($collection['end_balance'])) {
                $query->whereBetween('balance', [$collection['start_balance'], $collection['end_balance']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function payableReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Payable::query()->with('vendor');

            if (isset($collection['vendor_id']) && !empty($collection['vendor_id'])) {
                $query->where('vendor_id', $collection['vendor_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('paid_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('paid_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('paid_date', [$collection['start_date'], $collection['end_date']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function receivableReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Receivable::query()->with('customer');

            if (isset($collection['customer_id']) && !empty($collection['customer_id'])) {
                $query->where('customer_id', $collection['customer_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('received_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('received_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('received_date', [$collection['start_date'], $collection['end_date']]);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function expenseReport(array $params)
    {
        try {
            $startWeek = strtotime("last saturday midnight");
            $endWeek = strtotime("-1 week",$startWeek);

            $startWeek = date("Y-m-d",$startWeek);
            $endWeek = date("Y-m-d",$endWeek);

            $today = Carbon::today();
            $lastDay = Carbon::yesterday();
            $lastWeek = [$startWeek, $endWeek];
            $lastMonth =  Carbon::now()->subMonth()->format('m');
            $thisMonth = Carbon::now()->format('m');
            $thisYear = Carbon::now()->format('Y');
            $lastYear =  date("Y",strtotime("-1 year"));

            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Expense::query()->with('expenseType');

            if (isset($collection['expense_type_id']) && !empty($collection['expense_type_id'])) {
                $query->where('expense_type_id', $collection['expense_type_id']);
            }

            if (isset($collection['start_date']) && !empty($collection['start_date'])) {
                $query->where('expense_date', '>=', $collection['start_date']);
            }

            if (isset($collection['end_date']) && !empty($collection['end_date'])) {
                $query->where('expense_date', '<=', $collection['end_date']);
            }

            if (!empty($collection['start_date']) && !empty($collection['end_date'])) {
                $query->whereBetween('expense_date', [$collection['start_date'], $collection['end_date']]);
            }

            if (!empty($collection['timePeriod']) && !empty($collection['timePeriod'])) {

                switch ($collection['timePeriod']) {
                    case 'today':
                        $query->whereDate('expense_date', '=', $today);
                        break;
                    case 'lastDay':
                        $query->whereDate('expense_date', '=', $lastDay);
                        break;
                    case 'lastWeek':
                        $query->whereBetween('expense_date', $lastWeek);
                        break;
                    case 'lastMonth':
                        $query->whereMonth('expense_date', '=', $lastMonth);
                        break;
                    case 'thisMonth':
                        $query->whereMonth('expense_date', '=', $thisMonth);
                        break;
                    case 'thisYear':
                        $query->whereYear('expense_date', $thisYear);
                        break;
                    case 'lastYear':
                        $query->whereYear('expense_date', $lastYear);
                        break;
                    default:
                        $query->where('expense_date', $today);
                }
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function staffReport(array $params)
    {
        try {
            $collection = collect($params);

            $shop_id = auth()->user()->shop_id;

            $query = Staff::query();

            if (isset($collection['name']) && !empty($collection['name'])) {
                $query->where('name', 'like', '%' . $collection['name'] . '%');
            }

            if (isset($collection['mobile']) && !empty($collection['mobile'])) {
                $query->where('mobile', $collection['mobile']);
            }

            return $query->where('shop_id', $shop_id)->get();

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

}
