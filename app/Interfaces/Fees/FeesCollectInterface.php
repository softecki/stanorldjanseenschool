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

    public function feesAssignedDetails();

    public function feesAssignedDetailsForPushTransactions();

    public function feesAssignedDetailsSearch($request);

    public function feesAssignedUnpaidDetails();

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
