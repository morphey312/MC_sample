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
import ClinicRepository from '@/repositories/clinic';
import NotificationMailingTemplateSettingClinic from "@/models/notification/mailing-setting/mailing-clinic";
import NotificationMailingTemplateSettingClinicRepository from "@/repositories/notification/mailing-setting/mailing-clinic";

export default {
    props: {
        notificationMailingTemplate: Object,
    },
    data() {
        return {
            model: new NotificationMailingTemplateSettingClinic(),
            repository: new NotificationMailingTemplateSettingClinicRepository({
                filters: {
                    notification_mailing_template_id: this.notificationMailingTemplate.id
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
