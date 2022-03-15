<template>
    <div class="diary-block card-record-section">
        <div
            v-if="$can('doctor-cabinet.outpatient-records')"
            class="paragraph">
            <model-form :model="model">
                <el-row :gutter="4">
                    <div class="card-record-line custom-wrapped">
                        <div class="card-record-field condition-record growable">
                        <div class="prefix">
                            {{ __('t: ') }}
                        </div>

                            <form-input
                                :autosize="true"
                                :entity="model"
                                placeholder=" "
                                property="temperature"
                            />
                            ℃,

                    </div>
                    <div class="card-record-field condition-record growable">
                        <div class="prefix">
                            {{ __('АД: ') }}
                        </div>

                            <form-input
                                :autosize="true"
                                placeholder=" "
                                :entity="model"
                                property="at"
                                class=""
                            />
                            /
                            <form-input
                                class="at2"
                                :autosize="true"
                                placeholder=" "
                                :entity="model"
                                property="at2"
                            />
                            {{ __('мм рт. ст.') }},


                    </div>
                    <div class="card-record-field condition-record growable">
                        <div class="prefix">
                            {{ __('ЧП: ') }}
                        </div>

                            <form-input
                                :autosize="true"
                                :entity="model"
                                placeholder=" "
                                property="frequency"
                            />
                            {{ __('уд./мин') }}

                    </div>

                    <el-col :span="6">
                        <el-button
                            type="primary"
                            @click="save">
                            {{ __('Сохранить') }}
                        </el-button>
                    </el-col>
                    </div>
                </el-row>
            </model-form>
        </div>
        <condition-entries
            :records="entries"
            :readonly="readonly"/>
    </div>
</template>

<script>
import ConditionEntries from './condition-record/Entries.vue';
import ConditionRecord from "@/models/patient/card/condition-record";

export default {
    components: {
        ConditionEntries,
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
                return new ConditionRecord({
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
