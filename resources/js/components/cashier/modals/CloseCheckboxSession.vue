<template>
    <div>
        <form-select
            :entity="selectedShift"
            property="id"
            :options="checkboxCashboxesList"
            :label="__('Выберите кассу')"
        />
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="closeSession">
                {{ __('Закрыть смену') }}
            </el-button>
        </div>
    </div>
</template>
<script>

import Shift from "@/models/checkbox/shift";

export default {
    props: {
        activeShift: Object,
        checkboxCashboxes: Array,
    },
    data() {
        return {
            forCheckbox: false,
            selectedShift: new Shift(),
        }
    },
    computed: {
        checkboxCashboxesList() {
            let result = [];
            this.checkboxCashboxes.forEach((item) => {
                result.push({
                    id: item.id,
                    value: item.money_reciever_cashbox_name
                })
            })

            return result;
        }
    },
    watch: {
        ['selectedShift.id']() {
            this.selectedShift = this.checkboxCashboxes.find(item => item.id === this.selectedShift.id);
        },
    },
    methods: {
        closeSession() {
            let shift = this.selectedShift;
            this.$emit('endSession',shift);
        },
        cancel() {
            this.$emit('close');
        },
    },
}
</script>
