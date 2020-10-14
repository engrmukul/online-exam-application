<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Sale;
use App\Contracts\SaleContract;
use App\Models\SaleInvoice;
use App\Models\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class SaleRepository extends BaseRepository implements SaleContract
{
    /**
     * SaleRepository constructor.
     * @param Sale $model
     */
    public function __construct(Sale $model)
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
    public function listSale(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        //return $this->all($columns, $order, $sort);
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-warning btn-xs  float-left mr-1" href="' . route('sales.invoice', [$row->id]) . '" title="Sales Invoice"><i class="fa fa-file"></i> '. trans("common.invoice") . '</a>';

                $actions.= '<a class="btn btn-primary btn-xs" href="' . route('sales.edit', [$row->id]) . '" title="Sales Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('sales.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->editColumn('customer_name', function ($row) {
                return Customer::where('id', $row->customer_id)->first()->name;
            })
            ->editColumn('sale_date', function ($row) {
                return bnDate($row->sale_da);
            })
            ->editColumn('total_bill', function ($row) {
                return numberFormatWithCurrency($row->total_bill);
            })
            ->editColumn('total_discount', function ($row) {
                return numberFormatWithCurrency($row->total_discount);
            })
            ->editColumn('total_paid', function ($row) {
                return numberFormatWithCurrency($row->total_paid);
            })
            ->editColumn('total_due', function ($row) {
                return numberFormatWithCurrency($row->total_due);
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findSaleById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Sale|mixed
     */
    public function createSale(array $params)
    {
        try {
            $collection = collect($params);

            //dd($collection);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $note = 'Sales';

            $merge = $collection->merge(compact('created_by', 'shop_id','note'));

            $Sale = new Sale($merge->all());

            $Sale->save();

            //SAVE PURCHASE DETAILS
            $saleInvoiceArray = array();
            $stockArray = array();
            foreach ($collection['product_id'] as $key => $product){
                $saleInvoice['sale_id'] = $Sale->id;
                $saleInvoice['product_id'] = $product;
                $saleInvoice['quantity'] = $collection['quantity'][$key] ;
                $saleInvoice['unit_price'] = $collection['sale_unit_price'][$key];
                $saleInvoice['amount'] = $collection['amount'][$key];
                $saleInvoice['created_by'] = $created_by;
                $saleInvoice['created_at'] = date('Y-m-d');
                $saleInvoice['shop_id'] = $shop_id;

                //STOCK
                $stock['product_id'] = $product;
                $stock['quantity'] = '-'.$collection['quantity'][$key] ;
                $stock['created_by'] = $created_by;
                $stock['created_at'] = date('Y-m-d');
                $stock['shop_id'] = $shop_id;

                $saleInvoiceArray[] = $saleInvoice;
                $stockArray[] = $stock;
            }

            SaleInvoice::insert($saleInvoiceArray);
            Stock::insert($stockArray);

            Customer::find($collection['customer_id'])->increment('balance', $collection['total_due']);

            //DEDUCT PREVIOUS DUE
            if(isset($collection['due_paid_amount']) && !empty($collection['due_paid_amount'])){
                $totalDeduct = 0;
                foreach ($collection['due_paid_amount'] as $dueIndex => $due){
                    if($due > 0){
                        Sale::where('id', $collection['due_sale_id'][$dueIndex])->decrement('total_due', $due);
                        $totalDeduct = $totalDeduct + $due;
                    }
                }

                Customer::find($collection['customer_id'])->decrement('balance', $totalDeduct);
            }

            return $Sale;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param $id
     */
    public function getInvoice($id)
    {
        return Sale::with('salesInvoices','salesProducts','shop', 'customer')->where('id', $id)->get();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateSale(array $params)
    {
        $Sale = $this->findSaleById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Sale->update($merge->all());

        return $Sale;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteSale($id, array $params)
    {
        $sale = $this->findSaleById($id);

        $sale->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $sale->update($merge->all());

        return $sale;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
