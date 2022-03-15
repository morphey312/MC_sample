<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-date
                            :entity="model"
                            property="start"
                            :label="__('Начало лечения:')"
                        />
                        <form-select
                            v-if="doctors.length > 0"
                            :entity="model"
                            :options="doctors"
                            property="doctor_id"
                            :label="__('Врач')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-date
                            :entity="model"
                            property="end"
                            :label="__('Окончание лечения:')"
                        />
                    </el-col>
                </el-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>

export default {
    props: {
        model: {
            type: Object,
        },
        doctors: {
            type: Array,
            default: () => [],
        }
    }
}
</script>
