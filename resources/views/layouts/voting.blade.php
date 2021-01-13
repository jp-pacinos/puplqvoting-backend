<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') | Central Student Council - Voting App</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="/assets/lib/normalize.css/normalize.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            /* color: #3e3e3e; */
            background-color: rgba(252, 252, 252, 1);
            font-family: 'Open Sans', Segoe UI, sans-serif;
            font-size: 16px;
        }

        .antialiased {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            width: 55%;
            padding: 0 14px;
            margin: 0 auto;
        }

        .main {
            padding: 85px 0;
            line-height: 2.5;
            font-size: 1.2em;
        }

        /*
        Heaeder
        */

        .header {
            width: 100%;
            background-color: white;
            box-shadow: 0 0 20px 0px rgb(0 0 0 / 0.06);
            position: fixed;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            margin: 0 auto;
            padding: 10px 0;
        }

        .header .brand {
            display: flex;
        }

        .header .brand a {
            margin: auto;
        }

        .header .brand .logo {
            /* height: 48px; */
            margin: auto;
        }

        .header .brand h1 {
            font-size: 20px;
            font-weight: 300;
            margin: auto;
            padding: 0 15px;
        }

        /*
        media
        */
        @media (max-width: 768px) {
            .container {
                width: 70%;
            }
        }

        @media (max-width: 425px) {
            .container {
                width: 80%;
            }

            .main {
                line-height: 1.5;
            }

            .main h1 {
                margin-bottom: 0.8em;
            }

            .header-container h1 {
                font-size: 0.8em;
            }
        }

        @media (max-width: 375px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>

<body class="antialiased">
    <header class="header">
        <div class="container">
            <div class="header-container">
                <div class="brand">
                    <a href="/">
                        <img src="/logo.png" alt="pup logo" height="48" width="48" class="logo" />
                    </a>
                    <h1>CENTRAL STUDENT COUNCIL</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>

</html>
