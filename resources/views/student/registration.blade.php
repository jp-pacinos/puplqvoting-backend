@extends('layouts.voting')

@section('title', 'Student Registration')

@push('head')
<style>
    #auth_form form div {
        margin-bottom: 0.5em;
    }

    #auth_form form {
        margin-bottom: 0.5em;
    }

    label {
        font-size: 0.8em;
        font-weight: 500;
        color: rgba(107, 114, 128, 1);
    }

    input {
        display: block;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.4rem 0.75rem;
        font-size: 1.1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    input:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

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

    .btn-primary:disabled {
        opacity: 0.5;
        box-shadow: none;
    }
</style>
@endpush

@section('content')
<h1>Register in this election.</h1>
<p>Please fill up the form to complete the registration.</p>

<div id="auth_form">
    <form action="/registration" method="post" autoComplete="off">
        @csrf
        <div>
            <label>
                Student Number
                <span style="color: red">*</span>
            </label>
            <input type="text" name="student_number" placeholder="Enter your student number"
                value="{{ old('student_number') }}" required autoFocus />
        </div>
        <div>
            <label>
                First name
                <span style="color: red">*</span>
            </label>
            <input type="text" name="firstname" placeholder="Enter your first name" value="{{ old('firstname') }}"
                required />
        </div>
        <div>
            <label>
                Last name
                <span style="color: red">*</span>
            </label>
            <Input type="text" name="lastname" placeholder="Enter your last name" value="{{ old('lastname') }}"
                required />
        </div>
        <div>
            <label>
                Birthday
                <span style="color: red">*</span>
            </label>
            <input type="date" name="birthdate" value="{{ old('birthdate') }}" required />
        </div>

        @if ($errors->any())
        @foreach ($errors->all() as $error)
        <p style="color: red">{{ $error }}</p>
        @endforeach
        @endif

        @if (old('registered') ?? null)
        <p>
            <span style="color: green">You are now registered in this eleciton.</span>
            Please proceed to <a href="/" rel="prefetch">election page</a>.
        </p>
        @endif

        <div style="margin: 2em auto"></div>

        <button type="submit" class="btn btn-lg btn-primary">Submit</button>
    </form>
</div>
@endsection
