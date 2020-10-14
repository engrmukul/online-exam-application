<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Contracts\ProductContract;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class ProductRepository extends BaseRepository implements ProductContract
{
    /**
     * ProductRepository constructor.
     * @param Product $model
     */
    public function __construct(Product $model)
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
    public function listProduct(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        //return $this->all($columns, $order, $sort);
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('products.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('products.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->editColumn('category_name', function ($row) {
                return Category::where('id', $row->category_id)->first()->name;
            })
            ->editColumn('unit_name', function ($row) {
                return Unit::where('id', $row->unit_id)->first()->name;
            })
            ->editColumn('purchase_unit_price', function ($row) {
                return bnNumber($row->purchase_unit_price);
            })
            ->editColumn('sale_unit_price', function ($row) {
                return bnNumber($row->sale_unit_price);
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findProductById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Product|mixed
     */
    public function createProduct(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Product = new Product($merge->all());

            $Product->save();

            return $Product;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateProduct(array $params)
    {
        $Product = $this->findProductById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Product->update($merge->all());

        return $Product;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteProduct($id, array $params)
    {
        $product = $this->findProductById($id);

        $product->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $product->update($merge->all());

        return $product;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }

    public function getProduct()
    {
        return $this->model->where('shop_id', auth()->user()->shop_id)->get();
    }
}
