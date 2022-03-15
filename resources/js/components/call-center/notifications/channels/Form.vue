<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название канала')" />
                        <form-input
                            :entity="model"
                            property="account_name"
                            :label="__('Аккаунт')" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            options="channel_type"
                            property="type"
                            :label="__('Тип')" />
                        <form-input
                            :entity="model"
                            property="account_password"
                            type="password"
                            :label="isTelegram ? __('Токен') : __('Пароль')" />
                    </el-col>
                </el-row>
            </section>
            <template v-if="isEmail">
                <hr />
                <section>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <form-input
                                :entity="model.settings"
                                property="signature"
                                :label="__('Подпись')" />
                        </el-col>
                        <el-col :span="12">
                            <form-input
                                :entity="model.settings"
                                property="host"
                                :label="__('Адрес сервера')" />
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <form-input
                                :entity="model.settings"
                                property="port"
                                type="number"
                                :label="__('Порт')" />
                        </el-col>
                        <el-col :span="12">
                            <form-select
                                :entity="model.settings"
                                :clearable="true"
                                options="encryption_type"
                                property="encryption"
                                :label="__('Шифрование')" />
                        </el-col>
                    </el-row>
                </section>
            </template>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import CONSTANT from '@/constants';

export default {
    props: {
        model: Object
    },
    computed: {
        isEmail() {
            return this.model.type === CONSTANT.NOTIFICATION.CHANNEL.EMAIL;
        },
        isTelegram() {
            return this.model.type === CONSTANT.NOTIFICATION.CHANNEL.TELEGRAM;
        },
    },
}
</script>
