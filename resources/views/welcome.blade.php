<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>LINE Login</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
            .full-height {
                height: 100vh;
            }
            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }
            .position-ref {
                position: relative;
            }
            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }
            .content {
                text-align: center;
            }
            .title {
                font-size: 84px;
            }
            .links > a {
                display: inline-block;
                color: #fff;
                background-color: #00B900;
                padding: 15px 25px;
                font-size: 13px;
                font-weight: 600;
                border-radius: 8px;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
            .m-b-md {
                margin-bottom: 30px;
            }
            .effect{
            border-radius: 40px;
            width: 300px;
            height:116px;
            margin: 0 auto;
            overflow: hidden;
            background: #000;
            }
            .effect img{
            width: 100%;
            cursor: pointer;
            }
            .effect:hover img{
            opacity: 0.9;
            }
            .effect:active img{
            opacity: 0.7;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">

            <div class="content">
                <div class="title m-b-md">
                    LINE LOGIN
                </div>

                <div class="effect">
                    <a href="/linelogin"><img src="/btn_line_login.png" class="btn_login"></a>
                </div>
                
            </div>
        </div>
    </body>
</html>