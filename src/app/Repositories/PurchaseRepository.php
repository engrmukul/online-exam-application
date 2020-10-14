<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Contracts\PurchaseContract;
use App\Models\PurchaseInvoice;
use App\Models\Stock;
use App\Models\Vendor;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class PurchaseRepository extends BaseRepository implements PurchaseContract
{
    /**
     * PurchaseRepository constructor.
     * @param Purchase $model
     */
    public function __construct(Purchase $model)
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
    public function listPurchase(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-warning btn-xs  float-left mr-1" href="' . route('purchases.invoice', [$row->id]) . '" title="Purchase Invoice"><i class="fa fa-file"></i> '. trans("common.invoice") . '</a>';

                $actions.= '<a class="btn btn-primary btn-xs  float-left mr-1" href="' . route('purchases.edit', [$row->id]) . '" title="Purchase Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('purchases.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->editColumn('vendor_name', function ($row) {
                return Vendor::where('id', $row->vendor_id)->first()->name;
            })
            ->editColumn('total_bill', function ($row) {
                return bnNumber($row->total_bill);
            })
            ->editColumn('total_discount', function ($row) {
                return bnNumber($row->total_discount);
            })
            ->editColumn('total_paid', function ($row) {
                return bnNumber($row->total_paid);
            })
            ->editColumn('total_due', function ($row) {
                return bnNumber($row->total_due);
            })
            ->editColumn('purchase_date', function ($row) {
                return bnDate($row->purchase_date);
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findPurchaseById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Purchase|mixed
     */
    public function createPurchase(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Purchase = new Purchase($merge->all());

            $Purchase->save();

            //SAVE PURCHASE DETAILS
            $purchaseInvoiceArray = array();
            $stockArray = array();
            foreach ($collection['product_id'] as $key => $product){
                $purchaseInvoice['purchase_id'] = $Purchase->id;
                $purchaseInvoice['product_id'] = $product;
                $purchaseInvoice['quantity'] = $collection['quantity'][$key] ;
                $purchaseInvoice['unit_price'] = $collection['purchase_unit_price'][$key];
                $purchaseInvoice['amount'] = $collection['amount'][$key];
                $purchaseInvoice['created_by'] = $created_by;
                $purchaseInvoice['created_at'] = date('Y-m-d');
                $purchaseInvoice['shop_id'] = $shop_id;

                //STOCK
                $stock['product_id'] = $product;
                $stock['quantity'] = $collection['quantity'][$key] ;
                $stock['created_by'] = $created_by;
                $stock['created_at'] = date('Y-m-d');
                $stock['shop_id'] = $shop_id;

                $purchaseInvoiceArray[] = $purchaseInvoice;
                $stockArray[] = $stock;
            }

            PurchaseInvoice::insert($purchaseInvoiceArray);
            Stock::insert($stockArray);

            Vendor::find($collection['vendor_id'])->increment('balance', $collection['total_due']);

            return $Purchase;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param $id
     */
    public function getInvoice($id)
    {
        return Purchase::with('purchaseInvoices','purchaseProducts','shop', 'vendor')->where('id', $id)->get();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updatePurchase(array $params)
    {
        $Purchase = $this->findPurchaseById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Purchase->update($merge->all());

        return $Purchase;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deletePurchase($id, array $params)
    {
        $purchase = $this->findPurchaseById($id);

        $purchase->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $purchase->update($merge->all());

        return $purchase;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
