@extends('backend.auth.master')

@section('title')
    {{ $data['title'] }}
@endsection

@section('content')
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ ___('common.login') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ ___('common.welcome_back_please_login_to_your_account') }}</p>
        <div class="mx-auto mt-4 h-1 w-12 rounded-full bg-gradient-to-r from-brand-600 to-amber-400"></div>
    </div>

    <form action="{{ route('login.auth') }}" method="post" class="space-y-5">
        @csrf

        <div class="form-group">
            <label class="form-label" for="username">{{ ___('common.mobile_or_email') }} <span class="text-red-500">*</span></label>
            <input type="text" name="email" id="username" value="{{ old('email') }}"
                class="form-input @error('email') form-input-error @enderror"
                placeholder="{{ ___('common.enter_mobile_or_email') }}" autocomplete="username" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group" x-data="{ show: false }">
            <label class="form-label" for="password">{{ ___('common.password') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" id="password"
                    class="form-input pr-12 @error('password') form-input-error @enderror"
                    placeholder="••••••••" autocomplete="current-password" />
                <button type="button" class="btn-icon absolute right-1 top-1/2 -translate-y-1/2" @click="show = !show"
                    :aria-label="show ? 'Hide password' : 'Show password'">
                    <svg x-show="!show" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        @if (setting('recaptcha_status'))
            <div class="form-group">
                <span class="form-label">{{ ___('common.captcha') }} <span class="text-red-500">*</span></span>
                <div class="{{ $errors->has('g-recaptcha-response') ? 'rounded-lg ring-2 ring-red-500' : '' }}">
                    {!! app('captcha')->display() !!}
                </div>
                @if ($errors->has('g-recaptcha-response'))
                    <p class="form-error">{{ $errors->first('g-recaptcha-response') }}</p>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                <input class="rounded border-gray-300 text-brand-600 focus:ring-brand-600" type="checkbox" name="rememberMe" id="rememberMe" />
                <span>{{ ___('common.remember_me') }}</span>
            </label>
            <a class="auth-footer-link text-sm" href="{{ route('forgot-password') }}">{{ ___('common.forgot_password') }}</a>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3">
            {{ ___('common.login') }}
        </button>
    </form>

    @if (\Config::get('app.APP_DEMO'))
        <div class="mt-8 border-t border-gray-100 pt-6">
            <p class="mb-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Demo</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <form action="{{ route('login.auth') }}" method="post">
                    @csrf
                    <input name="email" type="hidden" value="superadmin@onest.com">
                    <input name="password" type="hidden" value="123456">
                    <input name="g-recaptcha-response" type="hidden" value="123456">
                    <button type="submit" class="btn-secondary w-full justify-center py-2.5 text-xs">{{ ___('common.login_as_superadmin') }}</button>
                </form>
                <form action="{{ route('login.auth') }}" method="post">
                    @csrf
                    <input name="email" type="hidden" value="admin@onest.com">
                    <input name="password" type="hidden" value="123456">
                    <input name="g-recaptcha-response" type="hidden" value="123456">
                    <button type="submit" class="btn-secondary w-full justify-center py-2.5 text-xs">{{ ___('common.login_as_admin') }}</button>
                </form>
                <form action="{{ route('login.auth') }}" method="post">
                    @csrf
                    <input name="email" type="hidden" value="student111@gmail.com">
                    <input name="password" type="hidden" value="123456">
                    <input name="g-recaptcha-response" type="hidden" value="123456">
                    <button type="submit" class="btn-secondary w-full justify-center py-2.5 text-xs">{{ ___('common.login_as_student') }}</button>
                </form>
                <form action="{{ route('login.auth') }}" method="post">
                    @csrf
                    <input name="email" type="hidden" value="guardian1@gmail.com">
                    <input name="password" type="hidden" value="123456">
                    <input name="g-recaptcha-response" type="hidden" value="123456">
                    <button type="submit" class="btn-secondary w-full justify-center py-2.5 text-xs">{{ ___('common.login_as_parent') }}</button>
                </form>
                <form action="{{ route('login.auth') }}" method="post" class="sm:col-span-2">
                    @csrf
                    <input name="email" type="hidden" value="teacher@onest.com">
                    <input name="password" type="hidden" value="123456">
                    <input name="g-recaptcha-response" type="hidden" value="123456">
                    <button type="submit" class="btn-secondary w-full justify-center py-2.5 text-xs">{{ ___('common.login_as_teacher') }}</button>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('script')
    {!! NoCaptcha::renderJs() !!}
@endsection
