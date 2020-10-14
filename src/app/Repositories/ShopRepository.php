<?php

namespace App\Repositories;

use App\Models\Shop;
use App\Contracts\ShopContract;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class ShopRepository extends BaseRepository implements ShopContract
{
    /**
     * ShopRepository constructor.
     * @param Shop $model
     */
    public function __construct(Shop $model)
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
    public function listShops(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        $query = $this->model->select();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';

                $actions.= '<a class="btn btn-primary btn-xs  float-left mr-1" href="' . route('shops.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('shops.destroy', [$row->id]).'" method="POST">
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
    public function findShopById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Shop|mixed
     */
    public function createShop(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $merge = $collection->merge(compact('created_by'));

            $shop = new Shop($merge->all());

            $shop->save();

            //CREATE SHOP OWNER ROLE USER
            $name = $params['owner_name'];
            $mobile = $params['mobile'];
            $username = 'PA'.$shop->id.$params['mobile'];
            $email = $params['email'];
            $password = Hash::make($mobile);
            $role = 'owner';
            $last_login = date('Y-m-d H:i:s');
            $created_by = $created_by;
            $shop_id = $shop->id;

            $mergeUserData = $collection->merge(compact('name','mobile','username','email','password','role','last_login','created_by','shop_id'));
            $user = new User($mergeUserData->all());
            $user->save();

            //SEND EMAIL TO SHOP OWNER WITH USERNAME AND PASSWORD
            //SEND SMS TO SHOP OWNER WITH USERNAME AND PASSWORD

            return $shop;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateShop(array $params)
    {
        $shop = $this->findShopById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $shop->update($merge->all());

        return $shop;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteShop($id, array $params)
    {
        $shop = $this->findShopById($id);

        $shop->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $shop->update($merge->all());

        return $shop;
    }
}
