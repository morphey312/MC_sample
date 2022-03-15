<template>
    <div class="sections-wrapper">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-select
                        :entity="model"
                        :options="siteServiceTypes"
                        property="site_service_type"
                        :label="__('Тип услуги на сайте')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="is_online"
                        :label="__('Онлайн видеоконсультация')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="for_foreigners"
                        :label="__('Услуга для иностранных граждан')"
                    />
                </el-col>
            </el-row>
        </section>
    </div>
</template>

<script>
export default {
    props: {
        model: Object,
    },
    data() {
        return {
            siteServiceTypes: this.$handbook.getOptions('site_service_type'),
        }
    },
}
</script>
