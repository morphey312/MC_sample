<template>
    <div class="checkbox-credentials-container">
        <checkbox-credentials-list
            ref="table"
            :employee="employee"
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
import ManageMixin from '@/mixins/manage';
import FormCreate from "./checkbox-credentials/FormCreate";
import FormEdit from "./checkbox-credentials/FormEdit";
import CheckboxCredentialsList from "./checkbox-credentials/List";

export default {
    components: {
        CheckboxCredentialsList,
    },
    mixins: [
        ManageMixin,
    ],
    props: {
        employee: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    employee: this.employee,
                },
                editForm: FormEdit,
                createHeader: __('Добавить данные для входа в Checkbox'),
                editHeader: __('Изменить данные для входа в Checkbox'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку данных для входа в Checkbox'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить данные для входа в Checkbox?'),
                deleted: __('Данные были успешно удалены'),
                created: __('Данные были успешно добавлены'),
                updated: __('Данные были успешно обновлены'),
            };
        },
    },
}
</script>
