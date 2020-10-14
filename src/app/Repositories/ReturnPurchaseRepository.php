<?php

namespace App\Repositories;

use App\Models\ReturnPurchase;
use App\Contracts\ReturnPurchaseContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class ReturnPurchaseRepository extends BaseRepository implements ReturnPurchaseContract
{
    /**
     * ReturnPurchaseRepository constructor.
     * @param ReturnPurchase $model
     */
    public function __construct(ReturnPurchase $model)
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
    public function listReturnPurchase(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        //return $this->all($columns, $order, $sort);
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs" href="' . route('return-purchases.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('return-purchases.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findReturnPurchaseById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return ReturnPurchase|mixed
     */
    public function createReturnPurchase(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $ReturnPurchase = new ReturnPurchase($merge->all());

            $ReturnPurchase->save();

            return $ReturnPurchase;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateReturnPurchase(array $params)
    {
        $ReturnPurchase = $this->findReturnPurchaseById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $ReturnPurchase->update($merge->all());

        return $ReturnPurchase;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteReturnPurchase($id, array $params)
    {
        $returnPurchase = $this->findReturnPurchaseById($id);

        $returnPurchase->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $returnPurchase->update($merge->all());

        return $returnPurchase;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
