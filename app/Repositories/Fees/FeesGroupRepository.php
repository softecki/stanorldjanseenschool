<?php

namespace App\Repositories\Fees;

use App\Interfaces\Fees\FeesGroupInterface;
use App\Models\Fees\FeesGroup;
use App\Traits\ReturnFormatTrait;

class FeesGroupRepository implements FeesGroupInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(FeesGroup $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function onlineAdmissionFeesMasters()
    {
        return $this->model->active()->where('online_admission_fees',1)->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->paginate(10);
    }

    public function store($request)
    {
        try {
            $row                          = new $this->model;
            $row->name                    = $request->input('name');
            $row->description             = $request->input('description');
            $row->status                   = $this->normalizeGroupStatus($request->input('status'));
            $row->online_admission_fees    = $this->normalizeOnlineAdmissionFlag($request->input('online_admission_fees'));
            $row->save();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }

    public function show($id)
    {
        return $this->model
            ->withCount(['feeAssigns', 'feeMasters'])
            ->with([
                'feeMasters' => function ($query) {
                    $query->with(['type:id,name'])
                        ->latest()
                        ->limit(40);
                },
                'feeAssigns' => function ($query) {
                    $query->with(['class:id,name', 'section:id,name'])
                        ->latest()
                        ->limit(40);
                },
            ])
            ->find($id);
    }

    public function update($request, $id)
    {
        try {
            $row                          = $this->model->findOrFail($id);
            $row->name                    = $request->input('name');
            $row->description             = $request->input('description');
            $row->status                   = $this->normalizeGroupStatus($request->input('status'));
            $row->online_admission_fees    = $request->has('online_admission_fees')
                ? $this->normalizeOnlineAdmissionFlag($request->input('online_admission_fees'))
                : (int) $row->online_admission_fees;
            $row->save();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    /**
     * Persist 0/1 only; legacy UI value 2 is stored as inactive (0).
     */
    private function normalizeGroupStatus($status): int
    {
        $n = (int) $status;

        return $n === 1 ? 1 : 0;
    }

    private function normalizeOnlineAdmissionFlag($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return ((int) $value) === 1 ? 1 : 0;
    }

    public function destroy($id)
    {
        try {
            $row = $this->model->find($id);
            if ($row === null) {
                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
            $row->delete();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
