<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Stock;
use App\Contracts\StockContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class StockRepository extends BaseRepository implements StockContract
{
    /**
     * StockRepository constructor.
     * @param Stock $model
     */
    public function __construct(Stock $model)
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
    public function listStock(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        //return $this->all($columns, $order, $sort);
        //$query = $this->shopWiseAllData();
        $query = $this->shopWiseAllData()
            ->select("*")
            ->selectRaw("SUM(quantity) as total_quantity")
            ->groupBy('product_id');

        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                /*$actions.= '<a class="btn btn-primary btn-xs" href="' . route('stocks.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('stocks.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';*/

                return $actions;
            })
            ->editColumn('product_name', function ($row) {
                return Product::where('id', $row->product_id)->first()->name;
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findStockById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Stock|mixed
     */
    public function createStock(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Stock = new Stock($merge->all());

            $Stock->save();

            return $Stock;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateStock(array $params)
    {
        $Stock = $this->findStockById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Stock->update($merge->all());

        return $Stock;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteStock($id, array $params)
    {
        $stock = $this->findShopById($id);

        $stock->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $stock->update($merge->all());

        return $stock;
    }
}
