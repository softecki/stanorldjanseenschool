<?php

namespace Modules\MainApp\Http\Repositories;

use PDO;
use App\Models\Tenant;
use App\Enums\Settings;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Modules\MainApp\Services\SaaSSchoolService;
use Modules\MainApp\Entities\School;
use Modules\MainApp\Entities\Package;
use Illuminate\Support\Facades\Session;
use Modules\MainApp\Entities\Subscription;
use Modules\MainApp\Http\Interfaces\SchoolInterface;
use App\Enums\SubscriptionStatus;

class SchoolRepository implements SchoolInterface
{
    use ReturnFormatTrait;
    private $model;

    public function __construct(School $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::all();
    }

    public function activeAll()
    {
        return $this->model::active()->get();
    }

    public function getAll()
    {
        return $this->model->latest()->paginate(Settings::PAGINATE);
    }

    public function store($request)
    {
        try {
            $request->merge(['package_id' => $request->package]);

            (new SaaSSchoolService)->store($request, 'admin');
            
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($request, $id)
    {
        try {

            $row                 = $this->model->findOrfail($id);
            $row->name           = $request->name;
            // // $row->package_id     = $request->package;
            // $row->address        = $request->address;
            // $row->phone          = $request->phone;
            // $row->email          = $request->email;
            $row->status         = $request->status;
            $row->save();

            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->find($id);
            $row->delete();

            $tenant = Tenant::where('id', $row->sub_domain_key)->first();
            $tenant->delete();

            $dbConnection = config('database.default'); // Get the default database connection name from config
            $dbConfig = config("database.connections.$dbConnection");

            $dbh = new PDO(
                "mysql:host={$dbConfig['host']};port={$dbConfig['port']}",
                $dbConfig['username'],
                $dbConfig['password']
            );

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $dbName = $tenant->tenancy_db_name;

            $sql = "DROP DATABASE IF EXISTS $dbName"; // Add IF EXISTS to avoid errors if the database doesn't exist
            $result = $dbh->exec($sql);

            DB::beginTransaction();
            DB::commit();
            if ($result !== false) {
                return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
            } else {
                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
