<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название провайдера')" />
                        <form-input
                            :entity="model"
                            property="account_name"
                            :label="__('Аккаунт')" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            options="mailing_provider_type"
                            property="type"
                            :label="__('Тип')" />
                        <form-input
                            :entity="model"
                            property="account_password"
                            type="password"
                            :label="__('Пароль')" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model.settings"
                            property="base_url"
                            :label="__('Базовый URL')" />
                    </el-col>
                    <el-col :span="12">
                        <form-input
                            :entity="model.settings"
                            property="timeout"
                            :label="__('Таймаут')" />
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
    computed: {
    },
}
</script>
