@extends('layouts.voting')

@section('title', 'Vote Completed')

@section('content')
<h1>PUPLCSC Voting System</h1>
<h2 style="margin: 0">Congratulations {{ $student->firstname }}!</h2>
<p style="margin: 0">Your vote has been validated and counted. We wish you a Happy Voting!</p>

<div style="margin: 2.5em auto"></div>

<h3 style="margin: 0">For manual counting of votes</h3>
<p style="margin: 0">Please download your vote copy for manual counting of votes.</p>
<a href="{{ $reportUrl }}" target="_blank">Download my Vote Results</a>
@endsection
