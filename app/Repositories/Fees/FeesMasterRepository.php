<?php

namespace App\Repositories\Fees;

use App\Enums\FineType;
use App\Interfaces\Fees\FeesMasterInterface;
use App\Models\Fees\FeesMaster;
use App\Models\Fees\FeesMasterChildren;
use App\Models\Fees\FeesMasterQuarter;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;

class FeesMasterRepository implements FeesMasterInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(FeesMaster $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()
            ->where('session_id', setting('session'))
            ->get();
    }

    public function allGroups()
    {
        $ids = $this->model->query()
            ->active()
            ->where('session_id', setting('session'))
            ->pluck('fees_group_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return \App\Models\Fees\FeesGroup::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    public function groupTypes($request)
    {
        return $this->model->active()
            ->where('session_id', setting('session'))
            ->where('fees_group_id', $request->id)
            ->get();
    }

    public function getPaginateAll()
    {
        return $this->model::query()
            ->with([
                'group:id,name',
                'type:id,name',
                'session:id,name',
            ])
            ->latest()
            ->where('session_id', setting('session'))
            ->paginate(10);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $sessionId = setting('session');
            if ($this->model->where('session_id', $sessionId)->where('fees_group_id', $request->input('fees_group_id'))->where('fees_type_id', $request->input('fees_type_id'))->first()) {
                DB::rollBack();

                return $this->responseWithError(___('alert.there_is_already_assigned'), []);
            }

            $row                 = new $this->model;
            $row->session_id     = $sessionId;
            $row->fees_group_id  = $request->input('fees_group_id');
            $row->fees_type_id   = $request->input('fees_type_id');
            $row->due_date       = $request->input('due_date');
            $row->amount         = $request->input('amount');
            $fineType            = (int) $request->input('fine_type', FineType::NONE);
            $row->fine_type      = $fineType;
            if ($fineType === FineType::NONE) {
                $row->percentage  = 0;
                $row->fine_amount = 0;
            } else {
                $row->percentage  = $request->input('percentage', 0);
                $row->fine_amount = $request->input('fine_amount', 0);
            }
            $row->status         = $this->normalizeMasterStatus($request->input('status'));
            $row->save();

            $feesChild                  = new FeesMasterChildren();
            $feesChild->fees_master_id  = $row->id;
            $feesChild->fees_type_id    = $request->input('fees_type_id');
            $feesChild->save();

            DB::commit();

            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model
            ->withCount('feesMasterChilds')
            ->with([
                'group:id,name',
                'type:id,name,code',
                'session:id,name',
                'feesMasterChilds' => function ($query) {
                    $query->with(['type:id,name'])->limit(20);
                },
            ])
            ->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $sessionId = setting('session');
            if ($this->model->where('session_id', $sessionId)->where('fees_group_id', $request->input('fees_group_id'))->where('fees_type_id', $request->input('fees_type_id'))->where('id', '!=', $id)->first()) {
                DB::rollBack();

                return $this->responseWithError(___('alert.there_is_already_assigned'), []);
            }

            $row                 = $this->model->findOrFail($id);
            $row->session_id     = $sessionId;
            $row->fees_group_id  = $request->input('fees_group_id');
            $row->fees_type_id   = $request->input('fees_type_id');
            $row->due_date       = $request->input('due_date');
            $row->amount         = $request->input('amount');
            $fineType            = (int) $request->input('fine_type', FineType::NONE);
            $row->fine_type      = $fineType;
            if ($fineType === FineType::NONE) {
                $row->percentage  = 0;
                $row->fine_amount = 0;
            } else {
                $row->percentage  = $request->input('percentage', 0);
                $row->fine_amount = $request->input('fine_amount', 0);
            }
            $row->status         = $this->normalizeMasterStatus($request->input('status'));
            $row->save();

            DB::commit();

            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    private function normalizeMasterStatus($status): int
    {
        return (int) $status === 1 ? 1 : 0;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->find($id);
            if ($row === null) {
                DB::rollBack();

                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
            FeesMasterChildren::where('fees_master_id', $row->id)->delete();
            FeesMasterQuarter::where('fees_master_id', $row->id)->delete();
            $row->delete();

            DB::commit();

            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function quartersOverview()
    {
        $p = $this->getPaginateAll();
        $ids = $p->getCollection()->pluck('id')->all();
        if ($ids === []) {
            return $p;
        }

        $byMaster = FeesMasterQuarter::query()
            ->whereIn('fees_master_id', $ids)
            ->orderBy('quarter')
            ->get()
            ->groupBy('fees_master_id');

        $newItems = $p->getCollection()->map(function (FeesMaster $m) use ($byMaster) {
            $rows = $byMaster->get($m->id, collect());
            $amt = (float) $m->amount;
            $qDefault = $amt > 0 ? $amt / 4 : 0.0;
            $uses = $rows->count() === 4;
            $q1 = $q2 = $q3 = $q4 = $qDefault;
            if ($uses) {
                $byQ = $rows->keyBy('quarter');
                $q1 = (float) ($byQ->get(1)?->amount ?? 0);
                $q2 = (float) ($byQ->get(2)?->amount ?? 0);
                $q3 = (float) ($byQ->get(3)?->amount ?? 0);
                $q4 = (float) ($byQ->get(4)?->amount ?? 0);
            }

            return [
                'id' => $m->id,
                'group' => $m->group,
                'type' => $m->type,
                'session' => $m->session,
                'amount' => $m->amount,
                'quater_one' => $q1,
                'quater_two' => $q2,
                'quater_three' => $q3,
                'quater_four' => $q4,
                'uses_custom_quarters' => $uses,
            ];
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $newItems,
            $p->total(),
            $p->perPage(),
            $p->currentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }

    public function syncMasterQuarters(int $masterId, array $amounts): array
    {
        $sessionId = (int) setting('session');
        $row = $this->model->query()->where('id', $masterId)->where('session_id', $sessionId)->first();
        if ($row === null) {
            return $this->responseWithError(___('alert.no_data_found'), []);
        }

        if (count($amounts) !== 4) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }

        DB::beginTransaction();
        try {
            $normalized = [];
            foreach ($amounts as $v) {
                $normalized[] = max(0, round((float) $v, 2));
            }
            $sum = array_sum($normalized);

            FeesMasterQuarter::query()->where('fees_master_id', $masterId)->delete();
            foreach ([1, 2, 3, 4] as $idx => $quarter) {
                FeesMasterQuarter::query()->create([
                    'fees_master_id' => $masterId,
                    'quarter' => $quarter,
                    'amount' => $normalized[$idx],
                ]);
            }

            $row->amount = $sum;
            $row->save();

            DB::commit();

            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
