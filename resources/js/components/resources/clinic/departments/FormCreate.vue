<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Общие')"
            name="info">
            <section>
                <department-form :model="model">
                    <div 
                        slot="buttons" 
                        class="form-footer text-right">
                        <el-button @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            type="primary"
                            @click="create">
                            {{ __('Создать') }}
                        </el-button>
                    </div>
                </department-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="model.isNew()"
            :label="__('Палаты')"
            name="rooms">
            <section>
                <department-rooms 
                    :department="model"
                    @created="roomCreated"
                    @cancel="cancel"
                    @completed="completed" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import Department from '@/models/department';
import DepartmentForm from './Form.vue';
import DepartmentRooms from './CreateRooms.vue';

export default {
    components: {
        DepartmentForm,
        DepartmentRooms,
    },
    data() {
        return {
            model: new Department(),
            activeTab: 'info',
            preventClose: false,
        }
    },
    mounted() {
        this.$safeClose(__('Вы не добавили ни одной палаты.'), () => {
            return this.preventClose;
        });
    },
    beforeDestroy() {
        this.$unsafeClose();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Данные сотрудника успешно сохранены'));
                this.activeTab = 'rooms';
                this.preventClose = true;
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        completed() {
            this.$emit('created', this.model);
        },
        roomCreated() {
            this.preventClose = false;
        },
    },
}
</script>
