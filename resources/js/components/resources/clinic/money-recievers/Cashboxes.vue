<template>
    <div>
        <cashboxes-list
            ref="table"
            :money-reciever="moneyReciever"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import CashboxesList from "./cashboxes/List";
import ManageMixin from '@/mixins/manage';
import FormCreate from "./cashboxes/FormCreate";
import FormEdit from "./cashboxes/FormEdit";

export default {
    components: {
        CashboxesList,
    },
    mixins: [
        ManageMixin,
    ],
    props: {
        moneyReciever: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    moneyReciever: this.moneyReciever,
                },
                editForm: FormEdit,
                createHeader: __('Добавить кассу к получателю средств'),
                editHeader: __('Изменить кассу в получателе средств'),
                width: '320px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку получателей средств'),
            };
        },
        onCreated(model) {
            this.$emit('cashboxes-updated');
        },
        onUpdated(model) {
            this.$emit('cashboxes-updated');
        },
        onDeleted(attributes) {
            this.$emit('cashboxes-updated');
        },
    }
}
</script>

<style scoped>

</style>
