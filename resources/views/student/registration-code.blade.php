@extends('layouts.voting')

@section('title', 'Student Registration')

@push('head')
<style>
    .btn {
        cursor: pointer;
        outline: none;
        text-decoration: none;
        border: 1px solid transparent;
        font-weight: 600;
        box-shadow: 0 0 7px 0px rgb(0 0 0 / 0.06);
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
            border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .btn-lg {
        font-size: 1em;
        padding: 10px 22px;
    }

    .btn:hover {
        box-shadow: 0 0 10px 0px rgb(0 0 0 / 20%);
    }

    .btn:active {
        transition: none;
        box-shadow: none;
        transform: scale(0.98);
    }

    .btn-primary {
        color: white;
        background-color: #dc3545;
    }
</style>
@endpush

@section('content')
<h2 style="margin: 1.8em 0 1em 0">Hello, {{ $studentName }}</h2>
<p style="margin: 0">
    You are now registered in this eleciton.</p>
<p style="margin: 0"> This Election requires you to enter code for vote validation, your code is
    <b>{{ $confirmationCode }}</b>.</p>
<p style="margin: 0">Please copy your confirmation code before clicking the button below.</p>

<div style="margin: 2.5em 0"></div>

<a href={{ route('student.index') }} class="btn btn-lg btn-primary" rel="prefetch">Go to Election Page</a>
@endsection
