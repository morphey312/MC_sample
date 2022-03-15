<template>
    <div class="specialities-container">
        <specialities-list 
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
import SpecialitiesList from './specialities/List.vue';
import FormCreate from './specialities/FormCreate.vue';
import FormEdit from './specialities/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SpecialitiesList,
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
                createHeader: __('Добавить специализацию'),
                editHeader: __('Изменить специализацию'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку специализаций'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту специализацию?'),
                deleted: __('Специализация была успешно удалена'),
                created: __('Специализация была успешно добавлена'),
                updated: __('Специализация была успешно обновлена'),
            };
        },
    },
}
</script>