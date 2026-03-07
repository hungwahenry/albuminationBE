@extends('emails.layouts.base')

@section('title', 'Login Code')

@section('content')
    <p class="email-text">Welcome back! Use the code below to log in to your account.</p>

    <div class="email-code">{{ $code }}</div>

    <p class="email-text">This code expires in 10 minutes. If you didn't request this, you can safely ignore this email.</p>
@endsection
