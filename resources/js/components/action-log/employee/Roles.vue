<script>
import DefaultLog from '../Default.vue';
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
            repository: new ActionLogRepository({filters: {role: this.id}}),
            attributes: {
                permissions: {
                    label: __('Доступы'),
                    formatter: (val) => {
                        return this.getPermissionLog(val);
                    }
                },
                name: __('Название'),
            },
        };
    },
    methods: {
        getPermissionLog(value) {
            let services = value.map(row => {
                return this.parseGroup(row);
            });
            return services.join('<br>');
        },
        parseGroup(group) {
            const serviceDivider = ";;"
            let data = group.split(serviceDivider);
            let groupName = data[0];
            let html = '<b>' + groupName + ':' + '</b>';
            delete data[0];
            data.forEach((permission) => {
                html = html + '<br>' + permission
            });
            return html;
        }
    }
};
</script>
