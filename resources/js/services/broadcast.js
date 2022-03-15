import Echo from 'laravel-echo';
import store from '@/store';
// import Soketi from '@soketi/soketi-js';
window.Pusher = require('pusher-js');
const io = require('socket.io-client');

const CHANNEL_GENERAL = 'App.General';
const CHANNEL_OPERATORS = 'App.Operators';
const CHANNEL_USER = 'App.User.{id}';
const CHANNEL_CHAT = 'App.Chat.Room.{room_id}';

const EVENTS = {
    '.App.Notification': 'notification',
    '.App.DaySheetLock': 'day_sheet_lock',
    '.App.AppointmentCreated': 'appointment_created',
    '.App.AppointmentUpdated': 'appointment_updated',
    '.App.AppointmentStatusDeleted': 'appointment_status_deleted',
    '.App.CallerHangup': 'caller_hangup',
    '.App.CallerResolved': 'caller_resolved',
    '.App.MemberPause': 'member_pause',
    '.App.RecordChanged': 'record_changed',
    '.App.NewPersonalTask': 'new_personal_task',
    '.App.CashboxUpdated': 'cashbox_updated',
    '.App.CheckboxCheckCreated': 'checkbox_check_created',
    '.App.NewSiteEnquiry': 'new_site_enquiry',
    '.App.DeferredResponse': 'deferred_response',
    '.App.WaitListRecordFound': 'wait_list_record_found',
    '.App.WaitListRecordProcessed': 'wait_list_record_processed',
    '.App.EhealthApplication': 'ehealth_application',
    '.App.GitlabPipelineFinished': 'gitlab_pipeline_finished',
};

const CHAT_EVENTS = {
    '.App.NewChatMessage': 'new_chat_message',
}
class Broadcast
{
    /**
     * Constructor
     */
    constructor() {
        this._echo = null;
        this._channel = null;
    }

    /**
     * Get user who is listening
     *
     * @returns {User}
     */
    get user() {
        return store.state.user;
    }

    /**
     * Check if connected
     *
     * @returns {bool}
     */
    get isConnected() {
        return this._echo !== null
            && this._echo.socketId() !== undefined;
    }

    /**
     * Initialize broadcast
     *
     * @returns {Echo}
     */
    get echo () {
        if (this._echo === null) {
            if (!store.state.token) {
                throw 'Auth token is not requested yet';
            }

            let config = window.appConfig.socket;
            let host = config.host || window.location.hostname;
            let wsPort = config.wsPort || window.location.port;
            let wssPort = config.wssPort || window.location.port;
            let key = config.key;

            this._echo = new Echo({
                broadcaster: 'pusher',
                key: key,
                wsHost: host,
                wsPort: wsPort,
                wssPort: wssPort,
                forceTLS: false,
                encrypted: true,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
            });
        }
        return this._echo;
    }

    /**
     * Disconnect from the socket server
     */
    disconnect() {
        if (this._echo) {
            this._echo.disconnect();
            this._echo = null;
        }
    }

    /**
     * Select general channel
     *
     * @returns {Broadcast}
     */
    atGeneral() {
        this._channel = this.echo.private(CHANNEL_GENERAL);
        return this;
    }
    /**
     * Select general channel
     *
     * @returns {Broadcast}
     */
     atChat(room_id) {
        this._channel = this.echo.private(CHANNEL_CHAT.replace('{room_id}', room_id));

        return this;
    }

    /**
     * Leave general channel
     *
     * @returns {Broadcast}
     */
    leaveGeneral() {
        this.echo.leave(CHANNEL_GENERAL);
        return this;
    }

    /**
     * Select operators channel
     *
     * @returns {Broadcast}
     */
    atOperators() {
        this._channel = this.echo.private(CHANNEL_OPERATORS);
        return this;
    }

    /**
     * Leave operators channel
     *
     * @returns {Broadcast}
     */
    leaveOperators() {
        this.echo.leave(CHANNEL_OPERATORS);
        return this;
    }

    /**
     * Select user's channel
     *
     * @returns {Broadcast}
     */
    atPrivate() {
        this._channel = this.echo.private(CHANNEL_USER.replace('{id}', this.user.id));
        return this;
    }

    /**
     * Leave private channel
     *
     * @returns {Broadcast}
     */
    leavePrivate() {
        if (this.user) {
            this.echo.leave(CHANNEL_USER.replace('{id}', this.user.id));
        }
        return this;
    }

    /**
     * Listen to specified event
     *
     * @param {string} event
     * @param {function} callback
     *
     * @returns {Broadcast}
     */
    listen(event, callback) {
        if (this._channel === null) {
            throw 'Channel is not selected';
        }
        this._channel.listen(event, callback.bind(this._channel));
        return this;
    }

    /**
     * Listen to subscriptions
     *
     * @param {array} subscriptions
     */
    listenSubscriptions(subscriptions) {
        for (let sub of subscriptions) {
            this.listen(sub.event, sub.callback);
        }
    }

    /**
     * Create subscriptions for all known events
     *
     * @param {function} callback
     * @param {function} error
     *
     * @returns {array}
     */
    createSubscriptions(callback, error = null, events) {
        let subs = [];
        if (error !== null) {
            let self = this;
            subs.push({
                event: '.subscription_error',
                callback: function (data) {
                    error(self.getChannelType(this.name), data);
                }
            });
        }
        Object.keys(events).forEach((key) => {
            subs.push({
                event: key,
                callback: (data) => {
                    callback(events[key], data);
                }
            });
        });
        return subs;
    }

    /**
     * Subscribe to all common channels
     *
     * @param {function} callback
     * @param {function} error
     *
     * @returns {Broadcast}
     */
    subscribe(callback, error = null) {
        let subs = this.createSubscriptions(callback, error, EVENTS);
        this.atGeneral().listenSubscriptions(subs);
        this.atPrivate().listenSubscriptions(subs);

        if (this.user.hasVoIP) {
            this.atOperators().listenSubscriptions(subs);
        }

        return this;
    }
    /**
     * Subscribe to chat room channel
     * @param {function} callback
     * @param {function} error
     * @param {number} room_id
     *
     * @returns {Broadcast}
     */
    subscribeChat(callback, error = null, room_id) {
        let subs = this.createSubscriptions(callback, error, CHAT_EVENTS);
        this.atChat(room_id).listenSubscriptions(subs);

        return this;
    }

    /**
     * Unsubscribe from all common channels
     *
     * @returns {Broadcast}
     */
    unsubscribe() {
        this.leaveGeneral();
        this.leavePrivate();

        if (this.user.hasVoIP) {
            this.leaveOperators();
        }

        return this;
    }
    leaveChatChannel(room_id) {
        if (this.user) {
            this.echo.leave(CHANNEL_USER.replace('{room_id}', room_id));
        }
        return this;
    }
    /**
     * Get channel type by name
     *
     * @param {string} name
     *
     * @return string
     */
    getChannelType(name) {
        if (name === 'private-' + CHANNEL_GENERAL) {
            return CHANNEL_GENERAL;
        }
        if (name === 'private-' + CHANNEL_OPERATORS) {
            return CHANNEL_OPERATORS;
        }
        if (name === 'private-' + CHANNEL_USER.replace('{id}', this.user.id)) {
            return CHANNEL_USER;
        }
        return name;
    }
}

export default new Broadcast();

export {
    CHANNEL_GENERAL,
    CHANNEL_OPERATORS,
    CHANNEL_USER,
};
