<template>
	 <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        :show-table-settings="false"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import handbook from '@/services/handbook';
import Specialization from '@/models/specialization';
import SpecializationClinic from '@/models/specialization/clinic';
import ClinicRepository from '@/repositories/clinic';
import NotificationTemplateSettingClinicRepository from "@/repositories/notification/Setting/clinic";
import NotificationTemplateSettingClinic from "@/models/notification/settings/clinic";

export default {
    props: {
        notificationTemplate: Object,
    },
    data() {
        return {
            model: new NotificationTemplateSettingClinic(),
            repository: new NotificationTemplateSettingClinicRepository({
                filters: {
                    notification_template_id: this.notificationTemplate.id
                }
            }),
            fields: [
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '15%',
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'active',
                    title: __('Статус'),
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    width: '8%',
                },
            ],
            clinic_list: new ClinicRepository(),
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
    },
}
</script>
