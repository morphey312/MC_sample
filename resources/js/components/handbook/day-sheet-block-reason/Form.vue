<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <form-input-i18n
                            :entity="model"
                            property="name"
                            :label="__('Причина блокировки')" />
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <form-checkbox
                            :entity="model"
                            property="for_operation"
                            :label="__('Использовать для операционной')" />
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
        model: Object
    },
}
</script>
