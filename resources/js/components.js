import Vue from 'vue';
import ScrollBar from 'vue2-scrollbar';
import ElementUI from 'element-ui';
import { Drag, Drop } from 'vue-drag-drop';
import { FunctionalCalendar } from 'vue-functional-calendar';
import wysiwyg from 'vue-wysiwyg';
import IEcharts from 'vue-echarts-v3/src/lite';
import vSelect from 'vue-select'

Vue.use(wysiwyg, {
    hideModules: {
        image: true,
    },
    forcePlainTextOnPaste: true,
});

Vue.component('drag', Drag);
Vue.component('drop', Drop);
Vue.use(ElementUI);

Vue.component('v-select', vSelect)
Vue.component('vue-scrollbar', ScrollBar);
Vue.component('menu-item', require('@/components/general/menu/Item.vue'));
Vue.component('form-row', require('@/components/general/form/Row.vue'));
Vue.component('form-input', require('@/components/general/form/Input.vue'));
Vue.component('form-input-search', require('@/components/general/form/SearchInput.vue'));
Vue.component('form-input-number', require('@/components/general/form/InputNumber.vue'));
Vue.component('form-input-address', require('@/components/general/form/Address.vue'));
Vue.component('form-input-address-elastic', require('@/components/general/form/AddressElastic.vue'));
Vue.component('form-input-i18n', require('@/components/general/form/InputI18N.vue'));
Vue.component('form-text', require('@/components/general/form/Text.vue'));
Vue.component('form-select', require('@/components/general/form/Select.vue'));
Vue.component('form-checkbox', require('@/components/general/form/Checkbox.vue'));
Vue.component('form-date', require('@/components/general/form/DatePicker.vue'));
Vue.component('form-time', require('@/components/general/form/TimePicker.vue'));
Vue.component('form-date-range', require('@/components/general/form/DateRangePicker.vue'));
Vue.component('form-switch', require('@/components/general/form/Switch.vue'));
Vue.component('form-upload', require('@/components/general/form/Upload.vue'));
Vue.component('form-yesno-addon', require('@/components/general/form/YesNoAddon.vue'));
Vue.component('filter-patient', require('@/components/general/form/filter/Patient.vue'));
Vue.component('manage-table', require('@/components/general/ManageTable.vue'));
Vue.component('page', require('@/components/general/Page.vue'));
Vue.component('search-filter', require('@/components/general/SearchFilter.vue'));
Vue.component('calendar', require('@/components/general/calendar/Calendar.vue'));
Vue.component('inline-input', require('@/components/general/form/inline/Input.vue'));
Vue.component('inline-datepicker', require('@/components/general/form/inline/Datepicker.vue'));
Vue.component('inline-checklist', require('@/components/general/form/inline/Checklist.vue'));
Vue.component('inline-button', require('@/components/general/form/inline/Button.vue'));
Vue.component('custom-transfer', require('@/components/general/form/transfer/Transfer.vue'));
Vue.component('toggle-link', require('@/components/general/ToggleLink.vue'));
Vue.component('drawer', require('@/components/general/Drawer.vue'));
Vue.component('svg-icon', require('@/components/general/SvgIcon.vue'));
Vue.component('content-placeholder', require('@/components/general/ContentPlaceholder.vue'));
Vue.component('no-data-placeholder', require('@/components/general/NoDataPlaceholder.vue'));
Vue.component('wait-search-placeholder', require('@/components/general/WaitSearchPlaceholder.vue'));
Vue.component('model-form', require('@/components/general/form/ModelForm.vue'));
Vue.component('form-error', require('@/components/general/form/FormError.vue'));
Vue.component('alert', require('@/components/general/Alert.vue'));
Vue.component('transfer-table', require('@/components/general/transfer-table/TransferTable.vue'));
Vue.component('sticky-footer', require('@/components/general/StickyFooter.vue'));
Vue.component('context-menu', require('@/components/general/ContextMenu.vue'));
Vue.component('file-attachments', require('@/components/general/FileAttachments.vue'));
Vue.component('functional-calendar', FunctionalCalendar);
Vue.component('ie-charts', IEcharts);
Vue.component('form-button', require('@/components/general/form/Button.vue'));
Vue.component('error-catcher', require('@/components/general/ErrorCatcher.vue'));
