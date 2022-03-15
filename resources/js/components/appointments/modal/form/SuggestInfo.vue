<template>
    <el-popover
        :title="title"
        placement="top-start"
        width="200"
        trigger="hover">
        <template v-if="isAppointment">
            <div class="props-list">
                <b>{{ __('Дата:') }}</b> {{ $formatter.dateFormat(suggestFrom.date) }}
            </div>
            <div class="props-list">
                <b>{{ __('Статус:') }}</b> {{ (suggestFrom.status ? suggestFrom.status.name : '') }}
            </div>
            <div class="props-list">
                <b>{{ __('Оператор:') }}</b> {{ suggestFrom.operator_name }}
            </div>
        </template>
        <template v-else-if="isCall">
            <div class="props-list">
                <b>{{ __('Дата:') }}</b> {{ $formatter.dateFormat(suggestFrom.date) }}
            </div>
            <div class="props-list">
                <b>{{ __('Результат:') }}</b> {{ suggestFrom.call_result_name }}
            </div>
            <div class="props-list">
                <b>{{ __('Оператор:') }}</b> {{ suggestFrom.operator_name }}
            </div>
        </template>
        <svg-icon 
            slot="reference"
            name="info-alt"
            class="icon-xtiny" />
    </el-popover>
</template>

<script>
import Appointment from '@/models/appointment';
import Call from '@/models/call';

export default {
    props: {
        suggestFrom: Object,
    },
    computed: {
        title() {
            if (this.isAppointment) {
                return __('У пациента была запись на прием');
            }
            if (this.isCall) {
                return __('У пациента был информационный звонок');
            }
            return '';
        },
        isAppointment() {
            return this.suggestFrom instanceof Appointment;
        },
        isCall() {
            return this.suggestFrom instanceof Call;
        },
    }
}
</script>