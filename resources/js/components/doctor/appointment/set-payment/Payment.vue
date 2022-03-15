<template>
    <section>
        <expected-payment-list
            ref="table"
            :appointment="appointment"
            @selection-changed="selectionChanged" />
        <div class="dialog-footer text-right" slot="buttons">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="$can('doctor-cabinet.start-course')"
                type="primary"
                :disabled="confirmDisabled"
                @click="completed">
                {{ __('Назначить оплату') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import ExpectedPaymentList from './List.vue';
import ServiceRepository from '@/repositories/appointment/service';

export default {
    components: {
        ExpectedPaymentList,
    },
    props: {
        appointment: Object,
    },
    data() {
        return {
            repository: new ServiceRepository(),
            changedRows: [],
        }
    },
    computed: {
        confirmDisabled(val) {
            return this.changedRows.length == 0;
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        selectionChanged(list) {
            this.changedRows = [...list];
        },
        getServicesAttributes() {
            return this.changedRows.map(row => {
                return {
                    id: row.id,
                    expected_payment: row.expected_payment,
                };
            });
        },
        completed() {
            this.repository.saveServicesAttributes(this.getServicesAttributes()).then((response) => {
                this.$info(__('Данные успешно обновлены'));
                this.$emit('completed');
            }).catch((error) => {
                this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
            });
        },
    }
}
</script>