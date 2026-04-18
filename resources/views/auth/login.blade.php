@extends('layouts.auth')

@section('title', ___('common.Login'))

@section('content')
<div
    class="flex min-h-screen flex-col justify-center px-4 py-12 sm:px-6 lg:px-8"
    x-data="{ showPass: false, loading: false }"
>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-600/25 ring-1 ring-white/20">
                <svg class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                </svg>
            </div>
            <h1 class="mt-5 text-2xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'School') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ ___('common.welcome_back_please_login_to_your_account') }}</p>
        </div>

        <div class="card shadow-xl shadow-gray-200/50 ring-1 ring-gray-100">
            <div class="card-body">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.auth') }}"
                    class="space-y-5"
                    @submit="loading = true"
                >
                    @csrf

                    <div>
                        <label for="email" class="form-label">{{ ___('common.mobile_or_email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="text"
                            autocomplete="username"
                            required
                            autofocus
                            value="{{ old('email') }}"
                            class="form-input @error('email') form-input-error @enderror"
                            placeholder="{{ ___('common.enter_mobile_or_email') }}"
                        />
                    </div>

                    <div>
                        <label for="password" class="form-label">{{ ___('common.password') }}</label>
                        <div class="relative w-full">
                            <input
                                id="password"
                                name="password"
                                :type="showPass ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="form-input w-full pr-11 @error('password') form-input-error @enderror"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 z-10 flex w-10 items-center justify-center rounded-r-lg text-gray-500 transition hover:text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40"
                                @click="showPass = !showPass"
                                :aria-label="showPass ? 'Hide password' : 'Show password'"
                            >
                                {{-- Eye (show password) --}}
                                <svg x-show="!showPass" class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{-- Eye slash (hide) --}}
                                <svg x-show="showPass" x-cloak class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if (setting('recaptcha_status'))
                        <div>
                            <span class="form-label">{{ ___('common.captcha') }}</span>
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
                            <input
                                class="rounded border-gray-300 text-brand-600 focus:ring-brand-600"
                                type="checkbox"
                                name="rememberMe"
                                id="rememberMe"
                                {{ old('rememberMe') ? 'checked' : '' }}
                            />
                            <span>{{ ___('common.remember_me') }}</span>
                        </label>
                        @if (Route::has('forgot-password'))
                            <a href="{{ route('forgot-password') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                {{ ___('common.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <div x-show="loading" x-cloak class="flex items-center justify-center gap-2 rounded-lg bg-blue-50 py-3 text-sm text-blue-800">
                        <svg class="h-5 w-5 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        {{ __('Signing in…') }}
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center py-3" x-bind:disabled="loading">
                        {{ ___('common.login') }}
                    </button>
                </form>
            </div>
        </div>

        @if (\Config::get('app.APP_DEMO'))
            <div class="mt-8 rounded-xl border border-gray-200 bg-white/80 p-4 shadow-sm ring-1 ring-gray-100 backdrop-blur-sm">
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

        @if (Route::has('register.page'))
            <p class="mt-6 text-center text-sm text-gray-600">
                {{ __('Need an account?') }}
                <a href="{{ route('register.page') }}" class="font-semibold text-blue-600 hover:text-blue-800">{{ ___('common.Register') }}</a>
            </p>
        @endif

        <p class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'School') }}
        </p>
    </div>
</div>
@endsection

@push('scripts')
    @if (setting('recaptcha_status'))
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
