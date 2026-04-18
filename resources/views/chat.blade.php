@extends('backend.master')

@section('title')
CHAT HERE
@endsection
@section('content')
<form method="POST" action="/chat">
    @csrf
    <input type="text" name="prompt" placeholder="Ask something..." required>
    <button type="submit">Send</button>
</form>
@if(session('reply'))
    <p><strong>ChatGPT:</strong> {{ session('reply') }}</p>
@endif
@endsection