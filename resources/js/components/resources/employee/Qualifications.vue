<template>
    <div class="qualifications-container">
        <qualifications-list 
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
import QualificationsList from './qualifications/List.vue';
import FormCreate from './qualifications/FormCreate.vue';
import FormEdit from './qualifications/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        QualificationsList,
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
                createHeader: __('Добавить курс'),
                editHeader: __('Изменить курс'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку курсов'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот курс?'),
                deleted: __('Курс был успешно удален'),
                created: __('Курс был успешно добавлен'),
                updated: __('Курс был успешно обновлен'),
            };
        },
    },
}
</script>