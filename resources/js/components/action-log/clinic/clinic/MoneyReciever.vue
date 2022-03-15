<script>
import DefaultLog from '../../Default.vue';
import ActionLogRepository from '@/repositories/action-log';

export default {
    extends: DefaultLog,
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({filters: {money_reciever: this.id}}),
            attributes: {
                name: __('Название получателя'),
                login: __('Логин в Checkbox'),
                password: __('Пароль в Checkbox'),
                cashbox_key: __('API ключ для кассы'),
            },
        };
    },
};
</script>
