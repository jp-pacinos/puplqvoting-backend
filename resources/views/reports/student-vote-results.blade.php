<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Vote Results for {{ $student->student_number }}</title>

    <style>
        @font-face {
            font-family: 'OpenSans';
            src: url('{{ storage_path('fonts/OpenSans-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'OpenSans Light';
            src: url('{{ storage_path('fonts/OpenSans-Light.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'OpenSans SemiBold';
            src: url('{{ storage_path('fonts/OpenSans-SemiBold.ttf') }}') format('truetype');
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: 'OpenSans';
            margin: auto 30px;
        }

        p {
            margin: 0;
            line-height: 1;
        }

        .header {
            height: 100px;
            margin-bottom: 30px;
        }

        .header div {
            height: 75px;
        }

        .header .logo {
            position: absolute;
            width: 75px;
        }

        .header .content {
            position: absolute;
            margin-left: 110px;
        }

        .header .content-title {
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .header .content-subtitle {
            font-size: 1rem;
            line-height: 1.2;
            font-family: 'OpenSans Light';
        }

        .main {
            margin-bottom: 50px;
        }

        .student {
            margin-bottom: 50px;
            text-transform: uppercase;
        }

        .student .student-name {
            font-size: 1.2rem;
        }

        .student .student-id {
            font-size: 0.8rem;
        }

        .candidates {
            margin-bottom: 50px;
        }

        .candidates .candidates-title {
            font-family: 'OpenSans SemiBold';
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .position .positions-table {
            margin-left: 40px;
            width: 100%;
        }

        .position .positions-table tr td:first-child {
            width: 200px;
            text-transform: uppercase;
            vertical-align: text-top;
        }

        .position .positions-table .position-party {
            font-family: 'OpenSans Light';
            font-size: 0.9rem;
            margin-left: 15px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="{{ storage_path('images/pup-logo.png') }}" alt="pup logo" height="80" width="80">
        </div>
        <div class="content">
            <p class="content-title">Central Student Council</p>
            <p class="content-subtitle">VOTING SYSTEM</p>
        </div>
    </div>

    <div class="main">
        <div class="student">
            <p class="student-name">{{ $student->fullname }}</p>
            <p class="student-id">{{ $student->student_number }}</p>
        </div>

        <div class="candidates">
            <p class="candidates-title">Your Candidates:</p>

            <div class="position">
                <table class="positions-table">
                    @foreach ($votes as $vote)
                    <tr>
                        <td>
                            <p>{{ $vote['position'] }}</p>
                        </td>
                        <td>
                            @foreach ($vote['officials'] as $official)
                            <p>{{ $official['name'] }} <span class="position-party">{{ $official['party'] }}</span></p>
                            @endforeach

                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">
                            <p style="margin: 20px 0;">--- Nothing Follows ---</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Validated: {{ $isVerified ?  'Yes' : 'No'}}</p>
        <p>Date Created: {{ $created_at ?? 'Unknown' }}</p>
    </div>
</body>

</html>
