<?php

namespace App\Repositories\Accounts;

use App\Enums\Settings;
use App\Models\Accounts\AccountingAccount;
use App\Models\Accounts\AccountAuditLog;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;

class AccountingAccountRepository
{
    use ReturnFormatTrait;

    public function __construct(
        protected AccountingAccount $model
    ) {}

    public function getAll(?\Illuminate\Http\Request $request = null)
    {
        $q = $this->model->with('parent')->withCount('children');
        if ($request !== null && $request->filled('q')) {
            $term = trim((string) $request->q);
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', '%'.$term.'%')
                    ->orWhere('code', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }
        if ($request !== null && $request->filled('type')) {
            $q->where('type', (string) $request->type);
        }
        if ($request !== null && $request->filled('status')) {
            $q->where('status', (int) $request->status);
        }

        return $q->orderBy('type')
            ->orderBy('name')
            ->paginate(Settings::PAGINATE)
            ->appends($request?->except('page') ?? []);
    }

    public function getTree()
    {
        $orderChildren = fn ($query) => $query->active()->orderBy('type')->orderBy('name');

        return $this->model->active()
            ->with([
                'children' => $orderChildren,
                'children.children' => $orderChildren,
                'children.children.children' => $orderChildren,
                'children.children.children.children' => $orderChildren,
            ])
            ->whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function getForSelect(string $type = null)
    {
        $q = $this->model->active()->orderBy('name');
        if ($type) {
            $q->ofType($type);
        }
        return $q->get();
    }

    public function getIncomeAccounts()
    {
        return $this->model->active()->income()->orderBy('name')->get();
    }

    public function getExpenseAccounts()
    {
        return $this->model->active()->expense()->orderBy('name')->get();
    }

    public function getAssetAccounts()
    {
        return $this->model->active()->asset()->orderBy('name')->get();
    }

    public function getLiabilityAccounts()
    {
        return $this->model->active()->liability()->orderBy('name')->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->create([
                'name'        => $request->name,
                'code'        => $request->code ?? $this->generateCode($request->type),
                'type'        => $request->type,
                'parent_id'   => $request->parent_id ?: null,
                'status'      => $request->status ?? 1,
                'description' => $request->description,
            ]);
            AccountAuditLog::log('created', 'accounting_accounts', $row->id, null, $row->toArray());
            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->findOrFail($id);
            if ($this->wouldCreateCycle((int) $id, $request->parent_id ? (int) $request->parent_id : null)) {
                DB::rollBack();
                return $this->responseWithError('Selected parent account would create an invalid account hierarchy.', []);
            }
            $old = $row->toArray();
            $row->update([
                'name'        => $request->name,
                'code'        => $request->code ?? $row->code,
                'type'        => $request->type,
                'parent_id'   => $request->parent_id ?: null,
                'status'      => $request->status ?? 1,
                'description' => $request->description,
            ]);
            AccountAuditLog::log('updated', 'accounting_accounts', $row->id, $old, $row->fresh()->toArray());
            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->findOrFail($id);
            $old = $row->toArray();
            $row->delete();
            AccountAuditLog::log('deleted', 'accounting_accounts', $id, $old, null);
            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    private function generateCode(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $last = $this->model->where('type', $type)->orderByDesc('id')->first();
        $num = $last ? (int) filter_var($last->code ?? '0', FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        return $prefix . '-' . $num;
    }

    private function wouldCreateCycle(int $accountId, ?int $parentId): bool
    {
        while ($parentId) {
            if ($parentId === $accountId) {
                return true;
            }

            $parentId = (int) ($this->model->whereKey($parentId)->value('parent_id') ?? 0);
        }

        return false;
    }
}
