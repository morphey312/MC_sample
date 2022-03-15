<template>
    <bonus-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </bonus-form>
</template>

<script>
import Employee from '@/models/employee';
import OperatorBonus from '@/models/employee/operator-bonus';
import BonusForm from './Form.vue';
import WarnExtChanges from '@/mixins/warn-external-changes';

export default {
    mixins: [
        WarnExtChanges,
    ],
    components: {
        BonusForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new Employee({
                id: this.item.id,
                full_name: this.item.full_name,
                operator_bonus: (this.item.operator_bonus ? this.item.operator_bonus : new OperatorBonus()),
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.confirmExternalOverwrite(() => {
                this.model.saveOperatorBonus().then((response) => {
                    this.$emit('saved', this.model);
                }).catch((e) => {
                    this.onSaveError(e);
                    this.$displayErrors(e);
                });
            });
        },
        getModelToWatch() {
            return this.model.operator_bonus;
        },
    },
}
</script>
