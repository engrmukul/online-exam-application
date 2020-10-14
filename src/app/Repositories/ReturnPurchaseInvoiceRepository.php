<?php

namespace App\Repositories;

use App\Models\ReturnPurchaseInvoice;
use App\Contracts\ReturnPurchaseInvoiceContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class ReturnPurchaseInvoiceRepository extends BaseRepository implements ReturnPurchaseInvoiceContract
{
    /**
     * ReturnPurchaseInvoiceRepository constructor.
     * @param ReturnPurchaseInvoice $model
     */
    public function __construct(ReturnPurchaseInvoice $model)
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
    public function listReturnPurchaseInvoice(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findReturnPurchaseInvoiceById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return ReturnPurchaseInvoice|mixed
     */
    public function createReturnPurchaseInvoice(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $ReturnPurchaseInvoice = new ReturnPurchaseInvoice($merge->all());

            $ReturnPurchaseInvoice->save();

            return $ReturnPurchaseInvoice;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateReturnPurchaseInvoice(array $params)
    {
        $ReturnPurchaseInvoice = $this->findReturnPurchaseInvoiceById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $ReturnPurchaseInvoice->update($merge->all());

        return $ReturnPurchaseInvoice;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteReturnPurchaseInvoice($id, array $params)
    {
        $returnPurchaseInvoice = $this->findReturnPurchaseInvoiceById($id);

        $returnPurchaseInvoice->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $returnPurchaseInvoice->update($merge->all());

        return $returnPurchaseInvoice;
    }
}
