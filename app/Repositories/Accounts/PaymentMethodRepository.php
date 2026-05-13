<?php

namespace App\Repositories\Accounts;

use App\Enums\Settings;
use App\Models\Accounts\PaymentMethod;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;

class PaymentMethodRepository
{
    use ReturnFormatTrait;

    public function __construct(
        protected PaymentMethod $model
    ) {}

    public function getAll()
    {
        return $this->model->orderBy('name')->paginate(Settings::PAGINATE);
    }

    public function getActive()
    {
        return $this->model->active()->orderBy('name')->get();
    }

    public function store($request)
    {
        try {
            $this->model->create([
                'name'        => $request->name,
                'description' => $request->description,
                // Create form defaults to "Active" in UI; preserve that if field is omitted.
                'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : 1,
            ]);
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $e) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        try {
            $this->model->findOrFail($id)->update([
                'name'        => $request->name,
                'description' => $request->description,
                'is_active'   => $request->boolean('is_active'),
            ]);
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $e) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        try {
            $this->model->findOrFail($id)->delete();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $e) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
