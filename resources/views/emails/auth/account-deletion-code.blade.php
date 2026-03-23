@extends('emails.layouts.base')

@section('title', 'Confirm Account Deletion')

@section('content')
    <p class="email-text">We received a request to permanently delete your Albumination account.</p>

    <div class="email-code">{{ $code }}</div>

    <p class="email-text">This code expires in 10 minutes. If you did not request account deletion, ignore this email — your account is safe.</p>
@endsection
