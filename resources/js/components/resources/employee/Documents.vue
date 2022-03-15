<template>
    <div class="documents-container">
        <documents-list 
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
import DocumentsList from './documents/List.vue';
import FormCreate from './documents/FormCreate.vue';
import FormEdit from './documents/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DocumentsList,
    },
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
                createHeader: __('Добавить документ'),
                editHeader: __('Изменить документ'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку документов сотрудника'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот документ?'),
                deleted: __('Документ был успешно удален'),
                created: __('Документ был успешно добавлен'),
                updated: __('Документ был успешно обновлен'),
            };
        },
    },
}
</script>