@extends('emails.layouts.base')

@section('title', 'Sign Up Code')

@section('content')
    <p class="email-text">Welcome to Albumination! Use the code below to create your account.</p>

    <div class="email-code">{{ $code }}</div>

    <p class="email-text">This code expires in 10 minutes. If you didn't request this, you can safely ignore this email.</p>
@endsection
