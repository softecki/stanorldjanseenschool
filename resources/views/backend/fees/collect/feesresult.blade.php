@foreach ($data['students'] as $item)
    <tr class="text-sm clickable-row" id="row_{{ $item->assignId }}" data-student-id="{{ $item->student->id }}" data-assign-id="{{ $item->assignId }}" role="button" tabindex="0">
        <td class="text-sm">{{ @$item->student->first_name }} {{ @$item->student->last_name }}</td>
        <td> <p class="text-sm text-gray-500">{{ @$item->fees_name }}</p></td>
        <td class="text-sm">{{ $item->class_name ?? $item->class->name ?? '—' }}</td>
        <td> @if (is_numeric($item->fees_amount))
                {{ number_format($item->fees_amount, 2, '.', ',') }}
            @else
                {{ @$item->fees_amount }}
            @endif </td>
        <td> @if (is_numeric($item->paid_amount))
                {{ number_format($item->paid_amount, 2, '.', ',') }}
            @else
                {{ @$item->paid_amount }}
            @endif </td>
        <td> @if (is_numeric($item->remained_amount))
                {{ number_format($item->remained_amount, 2, '.', ',') }}
            @else
                {{ @$item->remained_amount }}
            @endif </td>
        <td> @if(@$item->remained_amount > 0 )
                <span class="text-danger">Unpaid</span>
            @else
                <span class="text-success">Paid</span>
            @endif
        </td>
        @if (hasPermission('fees_collect_create'))
            <td class="action no-row-click">
                <a title="Collect Fees" href="javascript:void(0);" class="btn btn-sm btn-outline-primary btn-collect-fees" data-student-id="{{ $item->student->id }}" data-url="{{ route('fees-collect.collect', $item->student->id) }}">
                    <span class="icon mr-1"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                </a>
                <a title="Cancel (move to Cancelled Collect)" class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                   onclick="event.stopPropagation(); delete_row('fees-collect/deleteFees', {{ $item->assignId }}, true);">
                    <span class="icon mr-8"><i class="fa-solid fa-trash-can"></i></span>
                </a>
            </td>
        @endif
    </tr>
@endforeach
