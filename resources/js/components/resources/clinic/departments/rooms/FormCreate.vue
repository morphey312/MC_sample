<template>
    <room-form 
        :model="model"
        @place-created="placeCreated"
        @place-updated="placeUpdated" >
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </room-form>
</template>

<script>
import Room from '@/models/department/room';
import RoomForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import PlaceMixin from './mixins/place';

export default {
    mixins: [
        CreateMixin,
        PlaceMixin,
    ],
    components: {
        RoomForm,
    },
    props: {
        department: Object,
    },
    data() {
        return {
            model: new Room({
                department_id: this.department.id,
            }),
        }
    },
}
</script>