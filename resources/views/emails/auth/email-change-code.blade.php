@extends('emails.layouts.base')

@section('title', 'Verify Your New Email')

@section('content')
    <p class="email-text">Use the code below to verify your new email address.</p>

    <div class="email-code">{{ $code }}</div>

    <p class="email-text">This code expires in 10 minutes. If you didn't request an email change, you can safely ignore this email.</p>
@endsection
