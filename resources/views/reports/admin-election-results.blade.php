<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Election Results for {{ $year }}</title>

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

        .page-break {
            page-break-after: always;
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

        .title-container {
            margin-bottom: 50px;
        }

        .title {
            font-family: 'OpenSans SemiBold';
            font-size: 1.1rem;
        }

        .subtitle {
            font-family: 'OpenSans Light';
            font-size: 1rem;
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
            width: 180px;
            text-transform: uppercase;
            vertical-align: text-top;
        }

        .position .positions-table tr td:nth-child(2) {
            width: 110px;
            vertical-align: text-top;
            text-align: right;
            padding-right: 20px;
        }

        .position .positions-table .position-votes {
            font-family: 'OpenSans Light';
            font-size: 1rem;
        }

        .position .positions-table .position-party {
            font-family: 'OpenSans Light';
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .party-group {
            margin-bottom: 20px;
        }

        .party-title {
            font-family: 'OpenSans SemiBold';
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .computation {
            margin-bottom: 30px;
        }

        .computation-title {
            font-family: 'OpenSans Semibold';
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .computation-table tr td:first-child {
            width: 240px;
        }

        .computation-table tr td:nth-child(2) {
            /* font-family: 'OpenSans Light'; */
            text-align: right;
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
        <div class="title-container">
            <p class="title">Election Results for {{ $year }}</p>
            <p class="subtitle">Summary of votes</p>
        </div>

        <div class="candidates">
            <div class="position">
                <table class="positions-table">
                    @foreach ($summaryVotes as $officialsByPosition)
                    @foreach ($officialsByPosition['officials'] as $official)
                    <tr>
                        <td>
                            <p>{{ $officialsByPosition['position'] }}</p>
                        </td>
                        <td>
                            <p class="position-votes">
                                {{ $official['votes'] }} votes
                            </p>
                        </td>
                        <td>
                            <p>
                                {{ $official['name'] }}
                                <span class="position-party">{{ $official['party_name'] }}</span>
                            </p>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Date Created: {{ $createdAt }}</p>
    </div>

    <div class="page-break"></div>

    <div class="main">
        @foreach ($partiesVotes as $partyName => $officials)
        <div class="title-container">
            <p class="title">Election Results for {{ $year }}</p>
            <p class="subtitle">Votes by Party</p>
        </div>

        <div class="party-group">
            <div>
                <p class="party-title">{{ $partyName }}</p>
            </div>

            @php $partyTotalVotes = 0; @endphp

            <div class="candidates">
                <div class="position">
                    <table class="positions-table">
                        @foreach ($officials as $official)

                        @php $partyTotalVotes += $official['votes']; @endphp

                        <tr>
                            <td>
                                <p>{{ $official['position_name'] }}</p>
                            </td>
                            <td>
                                <p class="position-votes">
                                    {{ $official['votes'] }} votes
                                </p>
                            </td>
                            <td>
                                <p>
                                    {{ $official['name'] }}
                                    {{-- <span class="position-party">{{ $partyName }}</span> --}}
                                </p>
                            </td>
                        </tr>

                        @endforeach

                        <tr>
                            <td>
                                <p style="margin: 20px 0;">TOTAL VOTES</p>
                            </td>
                            <td>
                                <p class="position-votes" style="margin: 20px 0;">
                                    {{ $partyTotalVotes }} votes
                                </p>
                            </td>
                            <td>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                <p style="margin: 20px 0;">--- Nothing Follows ---</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        <div class="footer">
            <p>Date Created: {{ $createdAt }}</p>
        </div>

        <div class="page-break"></div>
    </div>

    <div class="main">
        <div class="title-container">
            <p class="title">Election Results for {{ $year }}</p>
            <p class="subtitle">Computation</p>
        </div>

        <div class="computation">
            <p class="computation-title">Election Status</p>

            <table class="computation-table">
                <tr>
                    <td>Student Registered</td>
                    <td>{{ $basicStats['registeredCount'] }}</td>
                </tr>
                <tr>
                    <td>Student Voted</td>
                    <td>{{ $basicStats['votedCount'] }}</td>
                </tr>
                <tr>
                    <td>Student Not Voted</td>
                    <td>{{ $basicStats['notVotedCount'] }}</td>
                </tr>
                <tr>
                    <td>Election Progress</td>
                    <td>{{ $basicStats['progress'] }}%</td>
                </tr>
            </table>
        </div>

        <div class="computation">
            <p class="computation-title">Total votes for every party</p>
            <p class="subtitle" style="margin-bottom: 10px; font-size: 0.9em">
                {{ $basicStats['votedCount'] }} Students voted * {{ $candidatesCountByParty }} Candidates per party =
                {{ $basicStats['votedCount'] * $candidatesCountByParty }} votes
            </p>
            @if ($basicStats['notVotedCount'] > 0)
            <p class="subtitle" style="margin-bottom: 10px; font-size: 0.9em">
                {{ $basicStats['notVotedCount'] }} Students not voted * {{ $candidatesCountByParty }} Candidates per
                party =
                {{ $basicStats['notVotedCount'] * $candidatesCountByParty }} votes left
            </p>
            @endif

            <table class="computation-table">
                @php $partiesTotalVotes = 0; @endphp

                @foreach ($totalCountByParty as $partyName => $count)
                @php $partiesTotalVotes += $count @endphp
                <tr>
                    <td>{{ $partyName }}</td>
                    <td>{{ $count }} votes</td>
                </tr>
                @endforeach

                <tr>
                    <td>Total votes</td>
                    <td>{{ $partiesTotalVotes }} votes</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Date Created: {{ $createdAt }}</p>
    </div>
</body>

</html>
