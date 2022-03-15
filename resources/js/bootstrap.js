import Vue from 'vue';
import '@/lang/languages';
import bootaxios from '@/services/axios';
import store from '@/store';
import '@/components';
import '@/services/service-worker';
import router from '@/router';
import moment from 'moment';
import permissions from '@/services/permissions';
import {Notification} from 'element-ui';
import ModalComponent from '@/components/general/ModalComponent.vue';
import * as formatter from '@/services/format';
import ticker from '@/services/ticker';
import eventHub from '@/services/event-hub';
import handbook from '@/services/handbook';
import '@/observers';
import {makeSafe, makeUnsafe} from '@/services/safe-close';
import '@/services/lodash';
import confirm from '@/services/confirm';
import logger from '@/services/logging';
import '@/mixins/patient-modal';
import '@/services/eicharts';
import { xorBy } from 'lodash';

Vue.prototype.__ = window.__;
Vue.prototype.$moment = moment;
Vue.prototype.$formatter = formatter;
Vue.prototype.$eventHub = eventHub;
Vue.prototype.$ticker = ticker;
Vue.prototype.$handbook = handbook;
Vue.prototype.$discountData = {
    reload: {
        service: false,
        analysis: false,
    },
    disabled: false,
    discountCard: null,
    oldDiscountCard: null,
    refreshDiscountType: 0,
    firstCalcDiscountCard: {
        service: true,
        analysis: true,
    },
};

const info = Vue.prototype.$info = (message, options = {}) => {
    store.commit('jourlanEntry', {type: 'info', message});
    Notification({
        message,
        title: __('Информация'),
        type: 'info',
        customClass: 'alert-info',
        ...options,
    });
}

const warning = Vue.prototype.$warning = (message, options = {}) => {
    store.commit('jourlanEntry', {type: 'warning', message});
    Notification({
        message,
        title: __('Предупреждение'),
        type: 'warning',
        customClass: 'alert-warning',
        ...options,
    });
}

const error = Vue.prototype.$error = (message, options = {}) => {
    store.commit('jourlanEntry', {type: 'error', message});
    Notification({
        message,
        title: __('Ошибка'),
        type: 'error',
        customClass: 'alert-error',
        duration: 10000,
        ...options,
    });
}

const unwrapError = (collection, attribute, error, prefix = '') => {
    if (error.length !== 0 && !_.isString(error[0])) {
        for (let attr in error[0]) {
            unwrapError(collection, attr, error[0][attr], `${prefix}${attribute}.`);
        }
    } else {
        collection[`${prefix}${attribute}`] = error;
    }
}

const formatErrors = (errors) => {
    let formatted = {};
    for (let attribute in errors) {
        unwrapError(formatted, attribute, errors[attribute]);
    }
    return formatted;
}

const displayErrors = Vue.prototype.$displayErrors = (errors) => {
    if (errors.errors) {
        errors = errors.errors;
    } else if (errors.response) {
        if (errors.response.response.status === 422) {
            errors = errors.response.getValidationErrors().errors;
        } else {
            error(__('При сохранении данных произошла ошибка'));
            return;
        }
    } else {
        return;
    }

    error(__('Пожалуйста, проверьте правильность введенных данных'));
    errors = formatErrors(errors);
    console.log(errors);
    logger.log('Validation failed', errors);
    eventHub.$emit('validationErrors', errors);
}

const clearErrors = Vue.prototype.$clearErrors = (errors) => {
    eventHub.$emit('validationErrors', {});
}

const getValidationError = Vue.prototype.$getValidationError = (response, key) => {
    let err = _.get(response, 'data.errors' + (key === null ? '' : `.${key}`), false);
    if (response.status === 422 && err !== false) {
        return err;
    }
    return null;
}

Vue.prototype.$confirm = confirm;
Vue.prototype.$confirmWhen = (when, message, confirmed, options) => {
    if (when) {
        confirm(message, confirmed, options);
    } else {
        confirmed();
    }
}

Vue.prototype.$modalComponent = (component, componentProps = {}, eventListeners = {}, modalOptions = {}) => {
    let ComponentClass = Vue.extend(ModalComponent);
    let propsData = {
        component,
        componentProps,
        modalOptions,
        eventListeners,
    };
    (new ComponentClass({propsData, store, router})).$mount();
}

Vue.prototype.$can = (action, some = true) => {
    if (_.isArray(action)) {
        return some ? permissions.canSome(action) : permissions.canEvery(action);
    }
    return permissions.can(action);
}

Vue.prototype.$isAccessLimited = (group) => {
    return !permissions.can(group + '.access');
}

Vue.prototype.$isCreationLimited = (group) => {
    return !permissions.can(group + '.create');
}

Vue.prototype.$isUpdateLimited = (group) => {
    return !permissions.can(group + '.update');
}

Vue.prototype.$canAccess = (group) => {
    return permissions.canSome([group + '.access', group + '.access-clinic']);
}

Vue.prototype.$canCreate = (group) => {
    return permissions.canSome([group + '.create', group + '.create-clinic']);
}

Vue.prototype.$canUpdate = (group) => {
    return permissions.canSome([group + '.update', group + '.update-clinic']);
}

Vue.prototype.$canDelete = (group) => {
    return permissions.canSome([group + '.delete', group + '.delete-clinic']);
}

Vue.prototype.$canManage = (action, clinics) => {
    if (permissions.can(action)) {
        return true;
    }

    if (permissions.can(action + '-clinic')) {
        return clinics.length === _.intersection(clinics, store.state.user.clinics).length;
    }

    return false;
}

Vue.prototype.$canManageAny = (action, clinics) => {
    if (permissions.can(action)) {
        return true;
    }

    if (permissions.can(action + '-clinic')) {
        return _.intersection(clinics, store.state.user.clinics).length;
    }

    return false;
}

Vue.prototype.$canProcessCalls = () => {
    return permissions.can('process-logs.create');
}

Vue.prototype.$safeClose = (message, condition = undefined) => {
    makeSafe(this, message, condition);
}

Vue.prototype.$unsafeClose = () => {
    makeUnsafe(this);
}

eventHub.$on('broadcast.notification', (data) => {
    let message = data.message.text?  data.message.text: data.message;
    info(message, {
        title: __('Системное уведомление'),
        duration: 30000,
        position: 'right',
        customClass: data.message.click_action? 'bottom-right action-link': 'bottom-right',
        iconClass: 'notification-new',
        onClick: () => {
            if (data.message.click_action) {
                eventHub.$emit(data.message.click_action, data.message.room_id);
            }
        }
    });
});

Vue.prototype.$isMobileNavigator = (() => {
    let userAgent = navigator.userAgent.toLowerCase();
    let isTablet = /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test(userAgent);
    return isTablet;
})();

bootaxios(error);
