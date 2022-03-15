<template>
    <room-form 
        :model="model"
        @place-created="placeCreated"
        @place-updated="placeUpdated">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </room-form>
</template>

<script>
import Room from '@/models/department/room';
import RoomForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import PlaceMixin from './mixins/place';

export default {
    mixins: [
        EditMixin,
        PlaceMixin,
    ],
    components: {
        RoomForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new Room({id: this.item.id}),
        };
    },
    mounted() {
        this.model.fetch();
    },
}
</script>