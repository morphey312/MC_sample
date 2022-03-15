import Vue from "vue";
import * as Sentry from "@sentry/vue";
import {Integrations} from "@sentry/tracing";
import CONSTANT from '@/constants';

class Logging {
    constructor() {
        this.enabled = false;
        this.user = null;
    }

    /**
     * Enable error capturing
     *
     * @param {object} user
     */
    enable(user) {
        this.user = user;

        if (this.enabled === false) {
            this.enabled = true;
            this.initSentry();
        }
    }

    /**
     * Init Sentry
     *
     */
    initSentry() {
        Sentry.init({
            Vue,
            tracingOptions: {
                trackComponents: true,
            },
            tracesSampleRate: 0.4,
            dsn: CONSTANT.SENTRY.DSN,
            integrations: [new Integrations.BrowserTracing()],
            logErrors: true,
            beforeBreadcrumb(breadcrumb, hint) {
                if (['ui.click', 'ui.input'].indexOf(breadcrumb.category) !== -1) {
                    return null;
                }
                if ('xhr' === breadcrumb.category && breadcrumb.data.status_code <= 401) {
                    return null;
                }
                return breadcrumb;
            },
        });

        Sentry.setUser(this.user);

        console.log('Sentry was enabled');
    }

    /**
     * Add log message
     *
     * @param {string} message
     * @param {object} data
     */
    log(message, data = null) {
        if (this.enabled) {
            Sentry.addBreadcrumb({
                category: 'message',
                message: this.formatMessage(message, data),
                level: 'log',
            });
        }
    }

    /**
     * Send message to sentry
     *
     * @param {string} message
     */
    sendMessage(message) {
        if (this.enabled) {
            Sentry.withScope(function (scope) {
                scope.setFingerprint(['User feedback']);
                Sentry.captureMessage(message);
            });
        }
    }

    /**
     * Format message
     *
     * @param {string} message
     * @param {object} data
     *
     * @returns {string}
     */
    formatMessage(message, data) {
        if (data === null) {
            return message;
        }
        let result = message;
        for (let key in data) {
            result += ', ' + key + ' = ' + data[key];
        }
        return result;
    }
}

export default new Logging();
