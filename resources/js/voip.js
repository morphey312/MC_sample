import SipContainer from '@/services/sip-ua/adapter/sipml5/container';
import '@/lang/languages';

const channel = new BroadcastChannel('voip_channel');
let container = null;

let handlers = {
    start(options) {
        if (container === null) {
            log(__('Устанавливаю соединение с сервером...'));
            container = new SipContainer({
                ...options,
                onConnected: () => {
                    channel.postMessage({message: 'connected'});
                    log(__('Соединение с сервером установлено'), 'connect');
                },
                onDisconnected: () => {
                    channel.postMessage({message: 'disconnected'});
                    log(__('Соединение с сервером потеряно'), 'disconnect');
                },
                onNewSession: (session, originator) => {
                    channel.postMessage({message: 'session', session, originator});
                    log(__('Инициирована сессия с') + ' ' + session.remote_identity.uri);
                },
                onCallStarted: (session) => {
                    channel.postMessage({message: 'accepted', session});
                    log(__('Начат разговор с') + ' ' + session.remote_identity.uri);
                },
                onCallEnded: (session) => {
                    channel.postMessage({message: 'ended', session});
                    log(__('Окончен разговор с') + ' ' + session.remote_identity.uri);
                },
                onCallFailed: (session) => {
                    channel.postMessage({message: 'failed', session});
                    log(__('Ошибка при созвоне с') + ' ' + session.remote_identity.uri, 'error');
                },
            });
            container.start();
        } else {
            setTimeout(() => {
                channel.postMessage({message: 'connected'});
            }, 100);
        }
    },
    answer() {
        if (container !== null) {
            log(__('Отвечаю на звонок...'));
            container.answer();
        }
    },
    terminate() {
        if (container !== null) {
            log(__('Завершаю звонок...'));
            container.terminate();
        }
    },
    call(to) {
        if (container !== null) {
            log(__('Вызываю абонента') + ' ' + to + '...');
            container.call(to);
        }
    },
    stop() {
        if (container !== null) {
            log(__('Отсоединяюсь от сервера...'));
            container.disconnect();
            container = null;
        }
    },
};

const log = (message, type = 'message') => {
    if (window.voipLog !== undefined) {
        window.voipLog(message, type);
    }
}

channel.onmessage = (event) => {
    let message = event.data.message;
    let args = event.data.args;
    if (handlers[message] !== undefined) {
        handlers[message](args);
    }
}

channel.postMessage({message: 'ready'});
localStorage.setItem('voip_container', 'true');

window.onbeforeunload = () => {
    localStorage.removeItem('voip_container');
    channel.postMessage({message: 'gone'});
    return false;
}
