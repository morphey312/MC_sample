<template>
    <cashbox-form
        :model="model">
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
    </cashbox-form>
</template>

<script>
import CashboxForm from "./Form";
import MoneyRecieverCashbox from "@/models/money-reciever/cashbox";
import EditMixin from '@/mixins/generic-edit';

export default {
    components: {
        CashboxForm,
    },
    mixins: [
        EditMixin,
    ],
    props: {
        item: Object,
    },
    data() {
        return {
            model: new MoneyRecieverCashbox({
                id: this.item.id,
            }),
        }
    },
    mounted() {
        this.model.fetch();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>

<style scoped>

</style>
