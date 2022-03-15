<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название')" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            options="active_status"
                            property="status"
                            :label="__('Статус')" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <p><b>{{ __('Койки') }}</b></p>
                <room-places
                    :room="model"
                    @place-created="placeCreated"
                    @place-updated="placeUpdated" />
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import RoomPlace from '@/models/department/room/place';
import RoomPlaces from './Places.vue';

export default {
    components: {
        RoomPlaces,
    },
    props: {
        model: {
            type: Object
        },
    },
    methods: {
        placeCreated(place) {
            this.$emit('place-created', place);
        },
        placeUpdated(data) {
            this.$emit('place-updated', data);
        }
    },
}
</script>
