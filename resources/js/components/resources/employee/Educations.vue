<template>
    <div class="educations-container">
        <educations-list
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
import EducationsList from './educations/List.vue';
import FormCreate from './educations/FormCreate.vue';
import FormEdit from './educations/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        EducationsList,
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
                createHeader: __('Добавить учебное заведение'),
                editHeader: __('Изменить учебное заведение'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку учебных заведений'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить это учебное заведение?'),
                deleted: __('Учебное заведение было успешно удалено'),
                created: __('Учебное заведение было успешно добавлено'),
                updated: __('Учебное заведение было успешно обновлено'),
            };
        },
    },
}
</script>
