<?php

namespace App\Repositories;

use App\Models\Staff;
use App\Contracts\StaffContract;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;

class StaffRepository extends BaseRepository implements StaffContract
{
    /**
     * StaffRepository constructor.
     * @param Staff $model
     */
    public function __construct(Staff $model)
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
    public function listStaff(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        $query = $this->shopWiseAllData();
        return Datatables::of($query)
            ->addColumn('action', function ($row) {
                $actions = '';
                if( $row->is_user == 'no'){
                    $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('staffs.add_as_user', [$row->id]) . '" title="Add as user"><i class="fa fa-arrow-alt-circle-right"></i>'. trans("common.add_as_user") . '</a>';
                }

                $actions.= '<a class="btn btn-primary btn-xs float-left mr-1" href="' . route('staffs.edit', [$row->id]) . '" title="Customer Edit"><i class="fa fa-pencil"></i>'. trans("common.edit") . '</a>';

                $actions.= '
                    <form action="'.route('staffs.destroy', [$row->id]).'" method="POST">
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
    public function findStaffById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }

    }

    /**
     * @param array $params
     * @return Staff|mixed
     */
    public function createStaff(array $params)
    {
        try {
            $collection = collect($params);

            $created_by = auth()->user()->id;

            $shop_id = auth()->user()->shop_id;

            $merge = $collection->merge(compact('created_by', 'shop_id'));

            $Staff = new Staff($merge->all());

            $Staff->save();

            return $Staff;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function updateStaff(array $params)
    {
        $Staff = $this->findStaffById($params['id']);

        $collection = collect($params)->except('_token');

        $updated_by = auth()->user()->id;

        $merge = $collection->merge(compact('updated_by'));

        $Staff->update($merge->all());

        return $Staff;
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function deleteStaff($id, array $params)
    {
        $staff = $this->findStaffById($id);

        $staff->delete();

        $collection = collect($params)->except('_token');

        $deleted_by = auth()->user()->id;

        $merge = $collection->merge(compact('deleted_by'));

        $staff->update($merge->all());

        return $staff;
    }

    /**
     * @return mixed
     */
    public function restore()
    {
        return $this->restoreOnlyTrashed();
    }

    /**
     * @param $staffId
     * @return mixed
     */
    public function addAsUser($staffId){
        try {

            $params = $this->findStaffById($staffId);

            $collection = collect($params)->except('_token');
            //CREATE SHOP STAFF ROLE USER
            $name = $params->name;
            $mobile = $params->mobile;
            $username = 'PA'.$params->shop_id.$params->mobile;
            $email = $params->shop_id.$params->mobile.'@gmail.com';
            $password = Hash::make($mobile);
            $role = 'staff';
            $last_login = date('Y-m-d H:i:s');
            $created_by = auth()->user()->id;
            $shop_id = $params->shop_id;

            $mergeUserData = $collection->merge(compact('name','mobile','username','email','password','role','last_login','created_by','shop_id'));
            $user = new User($mergeUserData->all());
            $user->save();

            return $user;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }
}
