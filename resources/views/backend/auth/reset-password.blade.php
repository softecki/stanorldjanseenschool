@extends('backend.auth.master')

@section('title')
    {{ $data['title'] }}
@endsection

@section('content')
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ ___('common.reset_passowrd') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ ___('common.welcome_back_please_reset_your_password') }}</p>
    </div>

    <form action="{{ route('reset.password') }}" method="post" class="space-y-4" x-data="{ show1: false, show2: false }">
        @csrf
        <input type="hidden" name="token" value="{{ $data['token'] }}">

        <div class="form-group">
            <label class="form-label" for="rp_email">{{ ___('common.email') }} <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="rp_email" value="{{ $data['email'] }}"
                class="form-input @error('email') form-input-error @enderror" placeholder="{{ ___('common.enter_your_email') }}" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="rp_password">{{ ___('common.password') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input :type="show1 ? 'text' : 'password'" name="password" id="rp_password"
                    class="form-input pr-12 @error('password') form-input-error @enderror" placeholder="••••••••" autocomplete="new-password" />
                <button type="button" class="btn-icon absolute right-1 top-1/2 -translate-y-1/2" @click="show1 = !show1" tabindex="-1">
                    <svg x-show="!show1" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="show1" x-cloak class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="rp_confirm">{{ ___('common.confirm_password') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input :type="show2 ? 'text' : 'password'" name="confirm_password" id="rp_confirm"
                    class="form-input pr-12 @error('confirm_password') form-input-error @enderror" placeholder="••••••••" autocomplete="new-password" />
                <button type="button" class="btn-icon absolute right-1 top-1/2 -translate-y-1/2" @click="show2 = !show2" tabindex="-1">
                    <svg x-show="!show2" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="show2" x-cloak class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            @error('confirm_password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary mt-2 w-full justify-center py-3">
            {{ ___('common.reset_passowrd') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a class="auth-footer-link" href="{{ route('login') }}">{{ ___('common.back_to_login') }}</a>
    </p>
@endsection

@section('script')
    {!! NoCaptcha::renderJs() !!}
@endsection
