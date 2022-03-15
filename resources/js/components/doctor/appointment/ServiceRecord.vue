<template>
    <div class="diary-block">
        <div
            v-if="$can('doctor-cabinet.outpatient-records')"
            class="paragraph">
            <model-form :model="model">
                <el-row :gutter="4">
                    <el-col :span="17">
                        <form-text
                            :autosize="true"
                            :entity="model"
                            :placeholder="__('Введите текст служебной записи')"
                            property="comment"
                        />
                    </el-col>
                    <el-col :span="7">
                        <el-button
                            type="primary"
                            @click="save">
                            {{ __('Добавить служебную запись') }}
                        </el-button>
                    </el-col>
                </el-row>
            </model-form>
        </div>
        <service-entries
            :records="entries"
            :readonly="readonly"/>
    </div>
</template>

<script>
import ServiceEntries from './service-record/Entries.vue';
import ServiceRecord from "@/models/patient/card/service-record";

export default {
    components: {
        ServiceEntries,
    },
    props: {
        appointment: Object,
        activeCard: Object,
        records: Array,
        readonly: {
            type: [Boolean, String],
            default: true,
        }
    },
    data() {
        return {
            model: this.initDiaryRecord(),
            entries: [...this.records],
        };
    },
    methods: {
        save() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Запись была успешно добавлена'));
                this.entries.push(this.model);
                this.$emit('add-diary-record', this.model);
                this.model = this.initDiaryRecord();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        initDiaryRecord() {
            if (this.activeCard) {
                return new ServiceRecord({
                    card_specialization_id: this.activeCard.id,
                    appointment_id: this.appointment.id,
                });
            }
            return null;
        },
    },
    watch: {
        records(val) {
            this.entries = [...val];
        },
    },
}
</script>
