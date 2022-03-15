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
                            @click="update">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                </department-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Палаты')"
            name="rooms">
            <section>
                <department-rooms 
                    :department="item"
                    :modal-component="modalComponent"
                    @cancel="cancel"
                    @department-updated="update"
                    @room-updated="roomsUpdated" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import DepartmentForm from './Form.vue';
import DepartmentRooms from './Rooms.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        DepartmentForm,
        DepartmentRooms,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: this.item.clone(),
            activeTab: 'info',
        };
    },
    mounted() {
        this.model.fetch(['permissions']);
    },
    methods: {
        roomsUpdated() {
            this.$emit('roomsUpdated');
        },
    },
}
</script>
