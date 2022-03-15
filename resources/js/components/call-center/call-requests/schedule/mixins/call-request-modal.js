import CreateForm from '../Create.vue';
import EditForm from '../Edit.vue';
import CancelForm from '../../Cancel.vue';
import CallRequest from '@/models/call-request';

export default {
    methods: {
        displayCreateCallRequestForm(modelAttributes, created = false, cancel = false) {
            this.$modalComponent(CreateForm, {modelAttributes}, {
                cancel: (dialog) => {
                    dialog.close();
                    cancel && cancel();
                },
                created: (dialog, call_request) => {
                    this.$info(__('Заявка на прозвон успешно создана'));
                    dialog.close();
                    created && created(call_request);
                },
            }, {
                header: __('Добавить заявку на прозвон'),
                width: '400px',
            });
        },
        displayEditCallRequestForm(modelAttributes, updated = false, cancel = false) {
            this.$modalComponent(EditForm, {modelAttributes}, {
                cancel: (dialog) => {
                    dialog.close();
                    cancel && cancel();
                },
                updated: (dialog, call_request) => {
                    this.$info(__('Заявка на прозвон успешно обновлена'));
                    dialog.close();
                    updated && updated(call_request);
                },
            }, {
                header: __('Редактировать заявку на прозвон'),
                width: '400px',
            });
        },
        displayDeleteCallRequestForm(item, deleted = false, cancel = false) {
            if (!(item instanceof CallRequest)) {
                item = new CallRequest(item);
            }
            this.$modalComponent(CancelForm, {item}, {
                cancel: (dialog) => {
                    dialog.close();
                    cancel && cancel();
                },
                canceled: (dialog) => {
                    this.$info(__('Заявка на прозвон успешно отменена'));
                    dialog.close();
                    deleted && deleted();
                },
            }, {
                header: __('Отменить заявку на прозвон'),
                width: '400px',
            });
        },
    },
};