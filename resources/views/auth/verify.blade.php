@extends('layouts.app')

@section('title', ___('common.Verify Your Email Address'))
@section('page_title', ___('common.Verify Your Email Address'))

@section('content')
    <div class="mx-auto max-w-lg">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ ___('common.Verify Your Email Address') }}</h2>
            </div>
            <div class="card-body space-y-4 text-sm text-gray-700">
                @if (session('resent'))
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800" role="status">
                        {{ ___('common.A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <p>
                    {{ ___('common.Before proceeding, please check your email for a verification link.') }}
                    {{ ___('common.If you did not receive the email') }},
                    @if (Route::has('verification.resend'))
                        <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="font-semibold text-brand-600 underline decoration-brand-600/30 underline-offset-2 hover:text-brand-700">
                                {{ ___('common.click here to request another') }}
                            </button>
                        </form>.
                    @endif
                </p>
            </div>
        </div>
    </div>
@endsection
