<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :label="__('Информация о пациенте')"
            name="patient-info" >
            <section>
                <record-form 
                    :model="model"
                    @close="close" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            v-if="$can('action-logs.access')"
            :lazy="true"
            :label="__('История изменений')"
            name="history">
            <section>
                <signal-record-log 
                    v-if="!model.isNew()"
                    :id="model.id" />
                <div v-else class="pt-40 pb-50">
                    <no-data-placeholder :message="__('Истории пока нет. Все изменения по сигнальным обозначениям будут храниться тут.')" />
                </div>
                <div class="form-footer text-right">
                    <el-button
                        :disabled="true"
                        type="default">
                        {{ __('Сохранить') }}
                    </el-button>
                    <el-button
                        :disabled="model.isNew()"
                        type="primary"
                        @click="close">
                        {{ __('Ознакомился, закрыть') }}
                    </el-button>
                </div>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import RecordForm from './Form.vue';
import SignalRecordLog from '@/components/action-log/SignalRecord.vue';

export default {
    components: {
        RecordForm,
        SignalRecordLog,
    },
    props: {
        model: Object,
    },
    data() {
        return {
            activeTab: 'patient-info',
        };
    },
    methods: {
        close() {
            this.$emit('close');
        },
    },
}
</script>