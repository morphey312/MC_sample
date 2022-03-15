<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>
    <title>MedCenter</title>
    <script>
        window.login = {!! json_encode([
            'user' => $userdata,
            'token' =>  $token,
            'ehealth_token' => $eh_token,
        ]) !!};
    </script>
</head>
<body>
    <p>Перенаправляю на сайт, подождите секунду...</p>
    <script src="{{ mix('js/eh-login.js') }}"></script>
</body>
</html>
