<template>
    <div class="diary-block">
        <div 
            v-if="$can('doctor-cabinet.outpatient-records')"
            class="paragraph">
            <model-form :model="model">
                <el-row :gutter="20">
                    <el-col :span="19">
                        <form-text
                            :autosize="true"
                            :entity="model"
                            property="comment"
                        />
                    </el-col>
                    <el-col :span="5">
                        <el-button
                            type="primary"
                            @click="save">
                            {{ __('Добавить запись') }}
                        </el-button>
                    </el-col>
                </el-row>
            </model-form>
        </div>
        <diary-entries 
            :records="entries"
            :readonly="readonly"/>
    </div>
</template>

<script>
import DiaryEntries from './diary/Entries.vue';
import DiaryRecord from '@/models/patient/card/diary-record';
import CONSTANT from '@/constants';

export default {
    components: {
        DiaryEntries,
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
                return new DiaryRecord({
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