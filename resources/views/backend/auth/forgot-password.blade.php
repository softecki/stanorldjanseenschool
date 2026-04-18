@extends('backend.auth.master')

@section('title')
    {{ $data['title'] }}
@endsection

@section('content')
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ ___('common.forgot_password') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ ___('common.enter_your_phone_or_email_to_recover_your_password') }}</p>
    </div>

    <form action="{{ route('forgot.password') }}" method="post" class="space-y-5">
        @csrf

        <div class="form-group">
            <label class="form-label" for="fp_email">{{ ___('common.email') }} <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="fp_email" value="{{ old('email') }}"
                class="form-input @error('email') form-input-error @enderror" placeholder="{{ ___('common.enter_your_email') }}" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3">
            {{ ___('common.send_link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a class="auth-footer-link" href="{{ route('login') }}">{{ ___('common.back_to_login') }}</a>
    </p>
@endsection
