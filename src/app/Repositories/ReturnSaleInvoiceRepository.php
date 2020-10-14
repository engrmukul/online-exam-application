<?php

namespace App\Repositories;

use App\Models\ReturnSaleInvoice;
use App\Contracts\ReturnSaleInvoiceContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class ReturnSaleInvoiceRepository extends BaseRepository implements ReturnSaleInvoiceContract
{
    /**
     * ReturnSaleInvoiceRepository constructor.
     * @param ReturnSaleInvoice $model
     */
    public function __construct(ReturnSaleInvoice $model)
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
    public function listReturnSaleInvoice(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findReturnSaleInvoiceById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return ReturnSaleInvoice|mixed
     */
    public function createReturnSaleInvoice(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $ReturnSaleInvoice = new ReturnSaleInvoice($merge->all());

            $ReturnSaleInvoice->save();

            return $ReturnSaleInvoice;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateReturnSaleInvoice(array $params)
    {
        $ReturnSaleInvoice = $this->findReturnSaleInvoiceById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $ReturnSaleInvoice->update($merge->all());

        return $ReturnSaleInvoice;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteReturnSaleInvoice($id, array $params)
    {
        $returnSaleInvoice = $this->findReturnSaleInvoiceById($id);

        $returnSaleInvoice->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $returnSaleInvoice->update($merge->all());

        return $returnSaleInvoice;
    }
}
