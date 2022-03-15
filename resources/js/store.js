import Vue from 'vue';
import Vuex from 'vuex';
import User from '@/models/user';
import {ProcessState} from '@/services/sip-ua/process-state';
import lts from '@/services/lts';
import tabsync from '@/services/tabsync';

Vue.use(Vuex);

let user = null;
let token = lts.token;
let call = null;
let processState = new ProcessState();
let clipboard = null;
let entryRoute = null;

if (lts.userData) {
    user = new User(lts.userData);
}

if (lts.processState) {
    processState = ProcessState.unserialize(lts.processState);
}

const state = {
    user,
    token,
    call,
    processState,
    clipboard,
    journal: [],
    entryRoute,
};

const mutations = {
    login(state, data) {
        state.user = new User(data);
        lts.userData = state.user.serialize();
    },

    updateToken(state, token) {
        if (state.token !== token) {
            state.token = token;
            lts.token = token;
            document.cookie = 'token=' + token.split(' ')[1];
            tabsync.sync('authToken', token);
        }
    },

    syncToken(state, token) {
        if (state.token !== token) {
            state.token = token;
            lts.token = token;
        }
    },

    logout(state) {
        state.user = null;
        state.token = null;
        state.clipboard = null;
        delete lts.userData;
        delete lts.token;
        delete lts.processLog;
    },

    callInfo(state, call) {
        state.call = call;
    },
    markAsReadedNotification(state, count) {
        state.user.unread_notifications = count;
        let tmpUserData = lts.userData;
        tmpUserData.unread_notifications = count;
        lts.userData = tmpUserData;
    },
    processState(state, processState) {
        state.processState = processState;
        lts.processState = processState.serialize();
    },

    clearProcessState(state) {
        state.processState = new ProcessState();
        delete lts.processState;
    },

    copyToClipboard(state, data) {
        state.clipboard = {...data};
    },

    clearClipboard(state) {
        state.clipboard = null;
    },

    jourlanEntry(state, entry) {
        state.journal.unshift({...entry, time: new Date()});
        if (state.journal.length > 100) {
            state.journal.pop();
        }
    },

    setEntryRoute(state, data) {
        state.entryRoute = data;
    },

    clearEntryRoute(state) {
        state.entryRoute = null;
    },
};

const store = new Vuex.Store({
    state,
    mutations
});

tabsync.on('authToken', (token) => {
    store.commit('syncToken', token);
});

export default store;
