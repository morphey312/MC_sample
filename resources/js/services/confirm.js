import Confirmation from '@/components/general/Confirmation.vue';
import Vue from "vue";

export default function confirm(message, confirmed, options = {}){
    let ComponentClass = Vue.extend(Confirmation);
    let propsData = {message, confirmed, ...options};
    (new ComponentClass({propsData})).$mount();
}
