@php
    $totals = [];
    $paid = [];
    $due = [];

    foreach ($collection_summary['collection_summary'] as $groupName => $summary) {
        $totals[] = $summary['total'];
        $paid[] = $summary['paid'];
        $due[] = $summary['total'] - $summary['paid'];
    }
@endphp

<tr>
    <th class="fw-bold">TOTAL</th>
    @foreach ($totals as $total)
        <td>{{ number_format($total, 2) }}</td>
    @endforeach
</tr>
<tr>
    <th class="fw-bold">AMOUNT PAID</th>
    @foreach ($paid as $paidAmount)
        <td>{{ number_format($paidAmount, 2) }}</td>
    @endforeach
</tr>
<tr>
    <th class="fw-bold">AMOUNT DUE</th>
    @foreach ($due as $dueAmount)
        <td>{{ number_format($dueAmount, 2) }}</td>
    @endforeach
</tr>
