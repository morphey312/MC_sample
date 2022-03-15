<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <room-form :model="model">
                <div 
                    slot="buttons"
                    class="mt-20">
                    <el-button
                        @click="add">
                        {{ __('Добавить палату') }}
                    </el-button>
                </div>
            </room-form>
        </section>
        <template v-if="hasRooms">
            <hr />
            <section>
                <room-list 
                    ref="table"
                    :department="repo"
                    @selection-changed="setActiveItem"
                    @loaded="refreshed" />
                <div class="mt-20">
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
            </section>
        </template>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                :disabled="!hasRooms"
                type="primary"
                @click="complete">
                {{ __('Завершить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import Department from '@/models/department';
import DepartmentRoom from '@/models/department/room';
import RoomForm from './rooms/Form.vue';
import FormEdit from './rooms/FormEdit.vue';
import RoomList from './rooms/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        RoomForm,
        RoomList,
    },
    props: {
        department: Object,
    },
    data() {
        return {
            hasRooms: false,
            model: new DepartmentRoom({
                department_id: this.department.id,
            }),
            repo: new Department({
                id: this.department.id,
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.saveRoom(() => {
                this.model = new DepartmentRoom({
                    department_id: this.department.id,
                });
                this.$info(__('Палата успешно добалена в отделение'));
                this.hasRooms = true;
                this.$emit('created');
                this.refresh();
            });
        },
        saveRoom(then) {
            this.$clearErrors();
            this.model.save().then((response) => {
                then();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        complete() {
            this.$emit('completed');
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editProps: {
                    limitClinics: this.$isCreationLimited('departments'),
                },
                editHeader: __('Изменить палату в отделении'),
                width: '600px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные палаты в отделении успешно обновлены'),
            };
        },
    },
}
</script>
