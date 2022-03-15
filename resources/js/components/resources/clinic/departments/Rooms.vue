<template>
    <div class="clinics-container">
        <room-list 
            ref="table"
            :department="department"
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
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="updateModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import RoomList from './rooms/List.vue';
import FormCreate from './rooms/FormCreate.vue';
import FormEdit from './rooms/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        RoomList,
    },
    props: {
        department: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    department: this.department,
                },
                editForm: FormEdit,
                createHeader: __('Добавить палату в отделение'),
                editHeader: __('Изменить палату в отделении'),
                width: '600px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку палат в отделения'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить палату из этого отделения?'),
                deleted: __('Плата успешно удалена из отделения'),
                created: __('Палата успешно добавлена в отделение'),
                updated: __('Данные палаты в отделении успешно обновлены'),
            };
        },
        cancel() {
            this.$emit('cancel');
        },
        onCreated(model) {
            this.$emit('room-updated');
        },
        onUpdated(model) {
            this.$emit('room-updated');
        },
        onDeleted(attributes) {
            this.$emit('room-updated');
        },
        updateModel() {
            this.$emit('department-updated');
        },
    },
}
</script>