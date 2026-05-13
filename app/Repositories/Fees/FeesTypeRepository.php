<?php

namespace App\Repositories\Fees;

use App\Interfaces\Fees\FeesTypeInterface;
use App\Models\Fees\FeesType;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;

class FeesTypeRepository implements FeesTypeInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(FeesType $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::query()
            ->with(['schoolClass:id,name'])
            ->latest()
            ->paginate(10);
    }

    public function store($request)
    {
        try {
            $row               = new $this->model;
            $row->name         = $request->input('name');
            $row->code         = $request->input('code');
            $row->description  = $request->input('description');
            $row->status       = $this->normalizeTypeStatus($request->input('status'));
            $row->class_id     = $this->normalizeClassId($request->input('class_id'));
            $row->save();
            $categoryIds = $this->normalizeStudentCategoryIds($request->input('student_category_ids', []));
            if ($message = $this->studentCategoriesExclusiveConflictMessage($categoryIds, null)) {
                return $this->responseWithError($message, []);
            }
            $row->studentCategories()->sync($categoryIds);
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model
            ->withCount('feeMasters')
            ->with([
                'schoolClass:id,name',
                'studentCategories:id,name',
                'feeMasters' => function ($query) {
                    $query->with(['group:id,name', 'session:id,name'])
                        ->latest()
                        ->limit(40);
                },
            ])
            ->find($id);
    }

    public function update($request, $id)
    {
        try {
            $row               = $this->model->findOrFail($id);
            $row->name         = $request->input('name');
            $row->code         = $request->input('code');
            $row->description  = $request->input('description');
            $row->status       = $this->normalizeTypeStatus($request->input('status'));
            $row->class_id     = $request->has('class_id')
                ? $this->normalizeClassId($request->input('class_id'))
                : $row->class_id;
            $row->save();
            if ($request->has('student_category_ids')) {
                $categoryIds = $this->normalizeStudentCategoryIds($request->input('student_category_ids', []));
                if ($message = $this->studentCategoriesExclusiveConflictMessage($categoryIds, (int) $row->id)) {
                    return $this->responseWithError($message, []);
                }
                $row->studentCategories()->sync($categoryIds);
            }
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    /**
     * Persist 0/1 only; legacy UI value 2 is stored as inactive (0).
     */
    private function normalizeTypeStatus($status): int
    {
        return (int) $status === 1 ? 1 : 0;
    }

    private function normalizeClassId($value): ?int
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        $n = (int) $value;

        return $n > 0 ? $n : null;
    }

    /**
     * @param  mixed  $raw
     * @return array<int, int>
     */
    private function normalizeStudentCategoryIds($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $ids = array_map('intval', $raw);
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        return $ids;
    }

    /**
     * Each student category may belong to at most one fee type (pivot row is unique on student_category_id).
     *
     * @param  array<int, int>  $categoryIds
     */
    private function studentCategoriesExclusiveConflictMessage(array $categoryIds, ?int $exceptFeesTypeId): ?string
    {
        if ($categoryIds === []) {
            return null;
        }

        $query = DB::table('fees_type_student_category as ftsc')
            ->join('fees_types as ft', 'ft.id', '=', 'ftsc.fees_type_id')
            ->join('student_categories as sc', 'sc.id', '=', 'ftsc.student_category_id')
            ->whereIn('ftsc.student_category_id', $categoryIds);

        if ($exceptFeesTypeId !== null) {
            $query->where('ftsc.fees_type_id', '!=', $exceptFeesTypeId);
        }

        $rows = $query->orderBy('sc.name')->get(['sc.name as category_name', 'ft.name as fee_type_name']);
        if ($rows->isEmpty()) {
            return null;
        }

        $parts = $rows->map(static fn ($r) => "{$r->category_name} → {$r->fee_type_name}")->unique()->implode('; ');

        return "Each student category can only be linked to one fee type. Already linked: {$parts}.";
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
