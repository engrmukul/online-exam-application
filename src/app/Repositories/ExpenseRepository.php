<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Contracts\ExpenseContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Yajra\DataTables\DataTables;

class ExpenseRepository extends BaseRepository implements ExpenseContract
{
    /**
     * ExpenseRepository constructor.
     * @param Expense $model
     */
    public function __construct(Expense $model)
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
    public function listExpense(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        $query = $this->shopWiseAllData()->with('expenseType');
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('expenses.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('expenses.destroy', [$row->id]).'" method="POST">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i> '. trans("common.edit") . '</button>
                    </form>
                ';

                return $actions;
            })
            ->editColumn('expense_type_id', function ($row) {
                return $row->expenseType->name;
            })
            ->make(true);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findExpenseById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Expense|mixed
     */
    public function createExpense(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Expense = new Expense($merge->all());

            $Expense->save();

            return $Expense;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateExpense(array $params)
    {
        $Expense = $this->findExpenseById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Expense->update($merge->all());

        return $Expense;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteExpense($id, array $params)
    {
        $expense = $this->findExpenseById($id);

        $expense->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $expense->update($merge->all());

        return $expense;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }
}
