@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <!--  table content start -->
        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ ___('settings.genders') }}</h4>
                    @if (hasPermission('gender_create'))
                        <a href="{{ route('banksAccounts.create') }}" class="btn btn-lg btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('common.add') }}</span>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered gender-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="purchase">{{ 'Bank Name' }}</th>
                                    <th class="purchase">{{ 'Account Name' }}</th>
                                    <th class="purchase">{{ 'Account Number' }}</th>
                                    <th class="purchase">{{ 'Status' }}</th>
                                    @if (hasPermission('gender_update') || hasPermission('gender_delete'))
                                        <th class="action">{{ ___('common.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['banksAccounts'] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">{{ ++$key }}</td>
                                    <td>{{ $row->bank_name }}</td>
                                    <td>{{ $row->account_name }}</td>
                                    <td>{{ $row->account_number }}</td>

                                    <td>
                                        @if ($row->status == App\Enums\Status::ACTIVE)
                                            <span class="badge-basic-success-text">{{ ___('common.active') }}</span>
                                        @else
                                            <span class="badge-basic-danger-text">{{ ___('common.inactive') }}</span>
                                        @endif
                                    </td>
                                    @if (hasPermission('gender_update') || hasPermission('gender_delete'))
                                        <td class="action">
                                            <a class="btn btn-outline-primary"
                                               href="{{ route('genders.edit', $row->id) }}"><span
                                                        class="icon mr-8"><i
                                                            class="fa-solid fa-pen-to-square"></i></span>
                                                {{ ___('common.edit') }}</a>

                                            <a class="btn btn-outline-primary"
                                               href="{{ route('genders.translate', $row->id) }}"><span
                                                        class="icon mr-8"><i
                                                            class="fa-solid fa-globe"></i></span>
                                                {{ ___('common.translate') }}</a>

                                            <a class="btn btn-outline-primary" href="javascript:void(0);"
                                               onclick="delete_row('genders/delete', {{ $row->id }})">
                                                                <span class="icon mr-8"><i
                                                                            class="fa-solid fa-trash-can"></i></span>
                                                <span>{{ ___('common.delete') }}</span>
                                            </a>


                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="100%" class="text-center gray-color">
                                        <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                                        <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                                        <p class="mb-0 text-center text-secondary font-size-90">
                                            {{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!--  table end -->
                </div>
            </div>
        </div>
        <!--  table content end -->

    </div>
@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
