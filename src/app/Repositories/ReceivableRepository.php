<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Receivable;
use App\Contracts\ReceivableContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class ReceivableRepository extends BaseRepository implements ReceivableContract
{
    /**
     * ReceivableRepository constructor.
     * @param Receivable $model
     */
    public function __construct(Receivable $model)
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
    public function listReceivable(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('receivables.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('receivables.destroy', [$row->id]).'" method="POST">
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
    public function findReceivableById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Receivable|mixed
     */
    public function createReceivable(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Receivable = new Receivable($merge->all());

            $Receivable->save();

            //UPDATE CUSTOMER BALANCE
            Customer::find($collection['customer_id'])->decrement('balance', $collection['amount']);

            return $Receivable;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateReceivable(array $params)
    {
        $Receivable = $this->findReceivableById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Receivable->update($merge->all());

        return $Receivable;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteReceivable($id, array $params)
    {
        $receivable = $this->findReceivableById($id);

        $receivable->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $receivable->update($merge->all());

        return $receivable;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
