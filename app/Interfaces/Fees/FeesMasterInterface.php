<?php

namespace App\Interfaces\Fees;

interface FeesMasterInterface
{

    public function all();
    public function allGroups();

    public function groupTypes($request);

    public function getPaginateAll();

    public function store($request);

    public function show($id);

    public function update($request, $id);

    public function destroy($id);

    /**
     * Paginated fee masters with effective quarter amounts for the quarters UI.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, array<string, mixed>>
     */
    public function quartersOverview();

    /**
     * @param  array<int, float|int|string>  $amounts  Four amounts for quarters 1–4
     * @return array{status: bool, message: string}
     */
    public function syncMasterQuarters(int $masterId, array $amounts): array;
}
