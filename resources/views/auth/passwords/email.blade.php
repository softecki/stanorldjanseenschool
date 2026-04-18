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
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif
                @if (Route::has('password.email'))
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                        @csrf

                        <div class="form-group">
                            <label for="email" class="form-label">{{ ___('common.Email Address') }}</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                class="form-input @error('email') form-input-error @enderror" />
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center py-3">
                            {{ ___('common.Send Password Reset Link') }}
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">{{ __('Password reset is not enabled for this application.') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
