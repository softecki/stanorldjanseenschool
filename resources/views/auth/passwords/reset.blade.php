@extends('layouts.app')

@section('title', ___('common.Reset Password'))
@section('page_title', ___('common.Reset Password'))

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ ___('common.Reset Password') }}</h2>
            </div>
            <div class="card-body">
                @if (Route::has('password.update'))
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label for="email" class="form-label">{{ ___('common.Email Address') }}</label>
                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                                class="form-input @error('email') form-input-error @enderror" />
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group" x-data="{ show: false }">
                            <label for="password" class="form-label">{{ ___('common.Password') }}</label>
                            <div class="relative">
                                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                    class="form-input pr-12 @error('password') form-input-error @enderror" />
                                <button type="button" class="btn-icon absolute right-1 top-1/2 -translate-y-1/2" @click="show = !show" tabindex="-1" aria-label="Toggle password">
                                    <svg x-show="!show" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group" x-data="{ show: false }">
                            <label for="password-confirm" class="form-label">{{ ___('common.Confirm Password') }}</label>
                            <div class="relative">
                                <input id="password-confirm" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                                    class="form-input pr-12" />
                                <button type="button" class="btn-icon absolute right-1 top-1/2 -translate-y-1/2" @click="show = !show" tabindex="-1" aria-label="Toggle password">
                                    <svg x-show="!show" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center py-3">
                            {{ ___('common.Reset Password') }}
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">{{ __('Password reset is not enabled for this application.') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
