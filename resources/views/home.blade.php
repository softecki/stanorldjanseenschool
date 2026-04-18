@extends('layouts.app')

@section('title', ___('common.Dashboard'))
@section('page_title', ___('common.Dashboard'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ ___('common.Dashboard') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ ___('common.You are logged in!') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="stat-card rounded-xl border border-gray-200 shadow-sm ring-1 ring-gray-100">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ___('common.Dashboard') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ __('Active') }}</p>
                </div>
                <div class="stat-card-icon bg-blue-100 text-blue-700">
                    <i class="fa-solid fa-chart-pie text-xl" aria-hidden="true"></i>
                </div>
            </div>
            <div class="stat-card rounded-xl border border-gray-200 shadow-sm ring-1 ring-gray-100">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('School') }}</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ config('app.name') }}</p>
                </div>
                <div class="stat-card-icon bg-amber-100 text-amber-800">
                    <i class="fa-solid fa-school text-xl" aria-hidden="true"></i>
                </div>
            </div>
            <div class="stat-card rounded-xl border border-gray-200 shadow-sm ring-1 ring-gray-100 sm:col-span-2 lg:col-span-1">
                <div class="flex flex-wrap gap-2">
                    @if (Route::has('dashboard'))
                        <a href="{{ route('dashboard') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-sm">
                            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                            {{ ___('common.dashboard') }}
                        </a>
                    @endif
                    @if (Route::has('my.profile'))
                        <a href="{{ route('my.profile') }}" class="btn-secondary inline-flex items-center gap-2 px-4 py-2.5 text-sm">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            {{ ___('common.my_profile') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>{{ __('Area') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('Account') }}</td>
                        <td><span class="badge-success">{{ __('Signed in') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('Session') }}</td>
                        <td><span class="badge-primary">{{ __('Active') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
