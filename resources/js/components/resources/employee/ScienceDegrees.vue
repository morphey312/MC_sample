<template>
    <div class="degrees-container">
        <degrees-list 
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
import DegreesList from './science-degrees/List.vue';
import FormCreate from './science-degrees/FormCreate.vue';
import FormEdit from './science-degrees/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DegreesList,
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
                createHeader: __('Добавить научную степень'),
                editHeader: __('Изменить научную степень'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку научных степеней'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту научную степень?'),
                deleted: __('Научная степень была успешно удалена'),
                created: __('Научная степень была успешно добавлена'),
                updated: __('Научная степень была успешно обновлена'),
            };
        },
    },
}
</script>