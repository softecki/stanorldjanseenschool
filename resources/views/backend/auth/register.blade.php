@extends('backend.auth.master')

@section('title')
    {{ $data['title'] }}
@endsection

@section('content')
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ ___('common.create_account') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ ___('common.please_sign_up_to_your_personal_account_if_you_want_to_use_all_our_premium_products') }}</p>
    </div>

    <form action="{{ route('register') }}" method="post" class="space-y-4" x-data="{ show1: false, show2: false }">
        @csrf

        <div class="form-group">
            <label class="form-label" for="reg_name">{{ ___('common.name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="reg_name" value="{{ old('name') }}"
                class="form-input @error('name') form-input-error @enderror" placeholder="{{ ___('common.enter_your_name') }}" />
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="reg_email">{{ ___('common.email') }} <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="reg_email" value="{{ old('email') }}"
                class="form-input @error('email') form-input-error @enderror" placeholder="{{ ___('common.enter_your_email') }}" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="reg_phone">{{ ___('common.phone') }}</label>
            <input type="text" name="phone" id="reg_phone" value="{{ old('phone') }}"
                class="form-input @error('phone') form-input-error @enderror" placeholder="{{ ___('common.enter_your_phone') }}" />
            @error('phone')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="reg_dob">{{ ___('common.date_of_birth') }} <span class="text-red-500">*</span></label>
            <input type="date" name="date_of_birth" id="reg_dob" value="{{ old('date_of_birth') }}"
                class="form-input @error('date_of_birth') form-input-error @enderror" />
            @error('date_of_birth')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <span class="form-label">{{ ___('common.gender') }} <span class="text-red-500">*</span></span>
            <div class="mt-2 flex flex-wrap gap-4 text-sm">
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input class="text-brand-600 focus:ring-brand-600" type="radio" name="gender" value="{{ App\Enums\Gender::MALE }}" checked />
                    <span>{{ ___('common.male') }}</span>
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input class="text-brand-600 focus:ring-brand-600" type="radio" name="gender" value="{{ App\Enums\Gender::FEMALE }}" />
                    <span>{{ ___('common.female') }}</span>
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input class="text-brand-600 focus:ring-brand-600" type="radio" name="gender" value="{{ App\Enums\Gender::OTHERS }}" />
                    <span>{{ ___('common.others') }}</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="reg_password">{{ ___('common.password') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input :type="show1 ? 'text' : 'password'" name="password" id="reg_password"
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
            <label class="form-label" for="confirm_password">{{ ___('common.confirm_password') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input :type="show2 ? 'text' : 'password'" name="confirm_password" id="confirm_password"
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

        <label class="flex cursor-pointer items-start gap-2 text-sm text-gray-600">
            <input class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600" type="checkbox" name="agree_with" id="agree_with" />
            <span>{{ ___('common.i_agree_to_privacy_policy_&_terms') }}</span>
        </label>

        <button type="submit" class="btn-primary mt-2 w-full justify-center py-3">
            {{ ___('common.register') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        {{ ___('common.already_have_an_account') }}
        <a class="auth-footer-link" href="{{ route('login') }}">{{ ___('common.login') }}</a>
    </p>
@endsection
