<?php

namespace App\Interfaces\Fees;

interface FeesCollectInterface
{

    public function all();

    public function getPaginateAll();

    public function store($request);

    public function show($id);

    public function showFeesAssignPerChildren($id);

    public function feesAssigned($id);

    /** @param \Illuminate\Http\Request|null $request Optional filters (name, class, start_date, end_date) — see feesAssignedDetails implementation */
    public function feesAssignedDetails(?\Illuminate\Http\Request $request = null);

    /** @param \Illuminate\Http\Request|null $request Optional filters: `name` (student), full-name CONCAT match supported */
    public function feesAssignedDetailsForPushTransactions(?\Illuminate\Http\Request $request = null);

    public function feesAssignedDetailsSearch($request);

    /** @param \Illuminate\Http\Request|null $request Optional filters: `name` (student), `q` (amendment description / parent) */
    public function feesAssignedUnpaidDetails(?\Illuminate\Http\Request $request = null);

    public function feesAssignedUnpaidDetailsSearch($request);

    public function update($request, $id);

    public function updateFeesAssignChildren($request, $id);

    public function updateAmendment($request, $id);

    public function destroy($id);

    public function getFeesAssignStudents($request);

    public function getFeesAssignStudentsAll();

    public function cancelFeesAssign($id);

    public function getCancelledCollects($perPage = 20);
    
    public function feesShow($request);

}
