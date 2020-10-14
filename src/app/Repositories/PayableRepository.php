<?php

namespace App\Repositories;

use App\Models\Payable;
use App\Contracts\PayableContract;
use App\Models\Vendor;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class PayableRepository extends BaseRepository implements PayableContract
{
    /**
     * PayableRepository constructor.
     * @param Payable $model
     */
    public function __construct(Payable $model)
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
    public function listPayable(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        //return $this->all($columns, $order, $sort);
        $query = $this->shopWiseAllData()->with('vendor');

        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('payables.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('payables.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i>'. trans("common.delete") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->editColumn('vendor_name', function ($row) {
                return $row->vendor->name;
            })
            ->editColumn('amount', function ($row) {
                return bnNumber($row->amount);
            })
            ->editColumn('paid_date', function ($row) {
                return bnDate($row->paid_date);
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findPayableById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Payable|mixed
     */
    public function createPayable(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Payable = new Payable($merge->all());

            $Payable->save();

            //UPDATE VENDOR BALANCE
            Vendor::find($collection['vendor_id'])->decrement('balance', $collection['amount']);


            return $Payable;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updatePayable(array $params)
    {
        $Payable = $this->findPayableById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Payable->update($merge->all());

        //UPDATE VENDOR BALANCE
        Vendor::find($collection['vendor_id'])->decrement('balance', $collection['amount']);

        return $Payable;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deletePayable($id, array $params)
    {
        $payable = $this->findPayableById($id);

        $payable->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $payable->update($merge->all());

        return $payable;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
