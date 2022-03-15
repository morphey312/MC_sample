<template>
    <div>
        <form-row name="select-all" v-if="availables.length != 0">
            <el-checkbox 
                v-model="checkAll"
                @change="handleCheckAllChange">
                <b>{{ __('Выбрать все назначения') }}</b>
            </el-checkbox>
        </form-row>
        <div v-else>
            <b>{{ __('Нет назначений  для печати') }}</b>
        </div>
        <div v-for="(prop, index) in availables" :key="index">
            <form-row name="analysis">
                <el-checkbox 
                    v-model="selectedItems"
                    :label="prop">
                    {{ labels[prop] }}
                </el-checkbox>
            </form-row>
        </div>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="selectedItems.length == 0">
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        analysisList: {
            type: Array,
            default: () => [],
        },
        medicineList: {
            type: Array,
            default: () => [],
        },
        procedureList: {
            type: Array,
            default: () => [],
        },
        physiotherapyList: {
            type: Array,
            default: () => [],
        },
        diagnosticsList: {
            type: Array,
            default: () => [],
        },
        consultations: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            checkAll: false,
            availables: this.getAvailables(),
            selectedItems: [],
            labels: {
                analysisList: __('Назначенные анализы'),
                medicineList:__('Назначенные медикаменты'),
                procedureList: __('Назначенные процедуры'),
                physiotherapyList: __('Назначенная физиотерапия'),
                diagnosticsList: __('Назначенная диагностика'),
                consultations: __('Назначенные консультации специалистов'),
            },
        }
    },
    methods: {
        handleCheckAllChange(val) {
            this.selectedItems = val ? this.availables : [];
        },
        getAvailables() {
            return Object.keys(this.$props).filter((prop) => {
                return this[prop].length != 0;
            });
        },
        cancel() {
            this.$emit('cancel');
        },
        confirm() {
            this.$emit('selected', this.selectedItems);
        },
    },
}
</script>