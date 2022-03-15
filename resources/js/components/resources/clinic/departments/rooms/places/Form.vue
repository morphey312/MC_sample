<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input-number
                            :entity="model"
                            property="number"
                            :label="__('Номер')" />
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
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import RoomPlace from '@/models/department/room/place';

export default {
    props: {
        model: {
            type: Object
        },
    },
}
</script>