<html>
    <head>
        <title>SIP клиент</title>
        <meta charset="utf-8">
        <style>
            html, body {
                padding: 0;
                margin: 0;
            }
            body {
                display: flex;
                height: 100vh;
                font: 12px Tahoma, 'sans-serif';
                flex-direction: column;
            }
            #status {
                flex: none;
                padding: 6px 10px;
                margin: 3px;
                background: #fafafa;
                line-height: 10px;
            }
            #status:before {
                display: inline-block;
                border-radius: 50%;
                background: #eee;
                content: ' ';
                width: 10px;
                height: 10px;
                margin-right: 5px;
            }
            #status.online:before {
                background: #71f74a;
            }
            #log {
                padding: 3px 10px;
                margin: 3px;
                overflow: auto;
            }
            #log div {
                margin: 3px 0;
                font-size: 10px;
                color: #777;
            }
            #log div.error, #log div.disconnect {
                color: #ff4b4b;
            }
        </style>
    </head>
    <body>
        <div id="status" class="offline">Оффлайн</div>
        <div id="log"></div>
    </body>
    <script>
        (function() {
            var status = document.getElementById('status');
            var logger = document.getElementById('log');
            window.voipLog = function(text, type) {
                var time = (new Date).toLocaleTimeString();
                var entry = document.createElement('div');
                entry.setAttribute('class', type);
                entry.innerHTML = time + ': ' + text;
                logger.appendChild(entry);
                
                if (type === 'connect') {
                    status.setAttribute('class', 'online');
                    status.innerHTML = 'Онлайн';
                } else if (type === 'disconnect') {
                    status.setAttribute('class', 'offline');
                    status.innerHTML = 'Оффлайн';
                }
            }
        })();
    </script>
    <script src="/vendor/sipml5/SIPml-api-no-ice.js"></script>
    <script src="{{ mix('js/voip.js') }}"></script>
</html>