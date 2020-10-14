<?php

namespace App\Repositories;

use App\Models\PurchaseInvoice;
use App\Contracts\PurchaseInvoiceContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class PurchaseInvoiceRepository extends BaseRepository implements PurchaseInvoiceContract
{
    /**
     * PurchaseInvoiceRepository constructor.
     * @param PurchaseInvoice $model
     */
    public function __construct(PurchaseInvoice $model)
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
    public function listPurchaseInvoice(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findPurchaseInvoiceById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return PurchaseInvoice|mixed
     */
    public function createPurchaseInvoice(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $PurchaseInvoice = new PurchaseInvoice($merge->all());

            $PurchaseInvoice->save();

            return $PurchaseInvoice;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updatePurchaseInvoice(array $params)
    {
        $PurchaseInvoice = $this->findPurchaseInvoiceById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $PurchaseInvoice->update($merge->all());

        return $PurchaseInvoice;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deletePurchaseInvoice($id, array $params)
    {
        $purchaseInvoice = $this->findPurchaseInvoiceById($id);

        $purchaseInvoice->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $purchaseInvoice->update($merge->all());

        return $purchaseInvoice;
    }
}
