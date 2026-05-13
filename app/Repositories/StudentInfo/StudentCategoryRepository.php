<?php

namespace App\Repositories\StudentInfo;

use App\Enums\ApiStatus;
use App\Traits\ReturnFormatTrait;
use App\Models\StudentInfo\StudentCategory;
use App\Interfaces\StudentInfo\StudentCategoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StudentCategoryRepository implements StudentCategoryInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(StudentCategory $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->paginate(10);
    }

    public function store($request)
    {
        try {
            $table              = $this->model->getTable();
            $store              = new $this->model;
            $store->name        = $request->name;
            if (Schema::hasColumn($table, 'description')) {
                $store->description = $request->filled('description') ? $request->description : null;
            }
            if (Schema::hasColumn($table, 'shortcode')) {
                $store->shortcode = $request->filled('shortcode') ? $request->shortcode : null;
            }
            $store->status      = $request->status;
            $store->save();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            Log::error('StudentCategoryRepository::store', [
                'message' => $th->getMessage(),
                'exception' => $th,
            ]);

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
            $table               = $this->model->getTable();
            $update              = $this->model->findOrfail($id);
            $update->name        = $request->name;
            if (Schema::hasColumn($table, 'description')) {
                $update->description = $request->filled('description') ? $request->description : null;
            }
            if (Schema::hasColumn($table, 'shortcode')) {
                $update->shortcode = $request->filled('shortcode') ? $request->shortcode : null;
            }
            $update->status      = $request->status;
            $update->save();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            Log::error('StudentCategoryRepository::update', [
                'message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return $this->responseWithError(___('school.Something went wrong'), []);
        }
    }

    public function destroy($id)
    {
        try {
            $bloodGroupDestroy = $this->model->find($id);
            $bloodGroupDestroy->delete();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            Log::error('StudentCategoryRepository::destroy', [
                'message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return $this->responseWithError(___('school.Something went wrong'), []);
        }
    }
}
