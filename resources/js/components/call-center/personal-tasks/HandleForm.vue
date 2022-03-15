<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <div class="paragraph">
                    <h4>{{ __('Что делать:') }}</h4>
                    {{ model.comment }}
                </div>
            </section>
            <hr />
            <section 
                v-if="model.patients.length !== 0"
                class="pb-0">
                <manage-table 
                    :fields="fields"
                    :repository="patients"
                    :enable-pagination="false">
                    <template
                        slot="full_name"
                        slot-scope="props">
                        <div class="has-icon">
                            <span class="ellipsis">
                                <a 
                                    href="#"
                                    @click.prevent="selectPatient(props.rowData)">
                                    {{ props.rowData.full_name }}
                                </a>
                            </span>
                            <svg-icon
                                name="user-alt" 
                                class="icon-tiny icon-blue"
                                @click="displayEditPatientForm(props.rowData.id)" />
                        </div>
                    </template>
                </manage-table>
            </section>
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <file-attachments :attachments="attachmentsData" />
                    </el-col>
                    <el-col :span="12">
                        <form-upload
                            ref="attachments"
                            :entity="model"
                            property="feedback_attachments"
                        />
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <form-text
                            :entity="model"
                            property="outcome"
                            :label="__('Добавить обратную звязь')"
                        />
                        <form-switch
                            :entity="model"
                            :options="statuses"
                            property="status"
                            :label="__('Статус задания')"
                        />
                    </el-col>
                </el-row>
            </section>
        </div>
        <slot name="buttons" :count-uploads="$refs.attachments ? $refs.attachments.countUploads : 0"></slot>
    </model-form>
</template>

<script>
import PatientRepository from '@/repositories/patient';
import CONSTANTS from '@/constants';
import SelectContactMixin from '@/components/call-center/mixins/select-contact';

export default {
    mixins: [
        SelectContactMixin,
    ],
    props: {
        model: {
            type: Object,
        },
        attachmentsData: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            statuses: this.$handbook.getOptions('personal_task_status').filter((opt) => opt.id !== CONSTANTS.PERSONAL_TASK.STATUS.NEW),
            patients: new PatientRepository({filters: {id: this.model.patients}}),
            fields: [{
                name: 'full_name',
                title: __('Пациенты'),
            }],
        };
    },
    methods: {
        selectPatient(patient) {
            this.selectPatientContact(patient);
            this.$emit('patient-selected', patient);
        },
    }
}
</script>