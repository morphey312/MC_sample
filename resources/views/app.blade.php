<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>
    <title>MedCenter</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
</head>
<body>
    <div class="content" id="app">
        <div :class="{ 'main-container': true, 'bg-login': $route.name === 'login' }">
            <router-view></router-view>
        </div>
    </div>
    <script>
        window.appConfig = {
            name: {!! json_encode(config('app.name')) !!},
            socket: {!! json_encode(config('socket.client')) !!},
            tokenTTL: {!! json_encode(config('jwt.ttl')) !!},
            elasticSearch: {!! json_encode(config('services.elasticsearch')) !!},
            depositSign: {!! json_encode(config('services.depositsign')) !!},
            env: {!! json_encode(config('app.env')) !!},
            ehealth: {!! json_encode(Illuminate\Support\Arr::except(config('services.ehealth'), ['client_secret'])) !!},
        };
    </script>
    <script src="/vendor/pdfjs/pdf.js"></script>
    <script src="/vendor/pdfjs/pdf-viewer.js"></script>
    <script src="/vendor/jspdf/jspdf.min.js"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
