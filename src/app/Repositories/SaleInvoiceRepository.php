<?php

namespace App\Repositories;

use App\Models\SaleInvoice;
use App\Contracts\SaleInvoiceContract;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class SaleInvoiceRepository extends BaseRepository implements SaleInvoiceContract
{
    /**
     * SaleInvoiceRepository constructor.
     * @param SaleInvoice $model
     */
    public function __construct(SaleInvoice $model)
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
    public function listSaleInvoice(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findSaleInvoiceById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return SaleInvoice|mixed
     */
    public function createSaleInvoice(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $SaleInvoice = new SaleInvoice($merge->all());

            $SaleInvoice->save();

            return $SaleInvoice;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateSaleInvoice(array $params)
    {
        $SaleInvoice = $this->findSaleInvoiceById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $SaleInvoice->update($merge->all());

        return $SaleInvoice;
    }

    /**
     * @param $id
     * @return bool|mixed
     */

    public function deleteSaleInvoice($id, array $params)
    {
        $saleInvoice = $this->findSaleInvoiceById($id);

        $saleInvoice->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $saleInvoice->update($merge->all());

        return $saleInvoice;
    }
}
