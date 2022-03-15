import Vue from 'vue';
import '@/bootstrap';
import router from '@/router';
import store from '@/store';

window.Vue = Vue;

const app = new Vue({
    el: '#app',
    router,
    store,
});
