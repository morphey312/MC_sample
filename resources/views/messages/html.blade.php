<html>
    <head>
        <style>
            section {padding: 10px 20px; font: 12px Arial, Sans-Serif;}
        </style>
    </head>
    <body>
        @if ($header)
            <section class="header">{!! $header !!}</section>
        @endif
        <section class="body">{!! $body !!}</section>
        @if ($footer)
            <section class="footer">{!! $footer !!}</section>
        @endif
    </body>
</html>