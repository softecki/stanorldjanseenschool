@if(isset($data['cancelled']) && $data['cancelled']->isNotEmpty())
    <ul class="list-group list-group-flush cancelled-collect-list">
        @foreach($data['cancelled'] as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                <div class="small">
                    <strong>{{ $item->first_name }} {{ $item->last_name }}</strong>
                    <span class="text-muted d-block">{{ $item->fees_name ?? '—' }}</span>
                    <span class="text-muted small">{{ $item->cancelled_at ? \Carbon\Carbon::parse($item->cancelled_at)->format('d M Y') : '' }}</span>
                </div>
                <span class="badge bg-secondary rounded-pill">{{ number_format($item->fees_amount ?? 0, 0) }} {{ Setting('currency_symbol') }}</span>
            </li>
        @endforeach
    </ul>
    @if($data['cancelled']->hasPages())
        <div class="p-2 small text-muted text-center">{{ $data['cancelled']->total() }} {{ __('entries') }}</div>
    @endif
@else
    <p class="p-3 mb-0 text-muted small">{{ ___('common.no_data_available') }}</p>
@endif
