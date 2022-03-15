<template>
    <el-row>
        <el-col :span="8">
            <section>
                <h3 class="mt-0 mb-20">{{ __('Аккредитация') }}</h3>
                <form-select
                    :entity="model"
                    options="accreditation_category"
                    property="accreditation_category"
                    :label="__('Категория')" />
                <form-date
                    :entity="model"
                    property="accreditation_issued_date"
                    :label="__('Дата выдачи')" />
                <form-date
                    :entity="model"
                    property="accreditation_expiry_date"
                    :label="__('Дата истечения срока')" />
                <form-input
                    :entity="model"
                    :required="hasAccreditation"
                    property="accreditation_order_no"
                    :label="__('Номер приказа')" />
                <form-date
                    :entity="model"
                    :required="hasAccreditation"
                    property="accreditation_order_date"
                    :label="__('Приказ от')" />
            </section>
        </el-col>
        <el-col :span="16">
            <section class="split-left">
                <h3 class="mt-0 mb-20">{{ __('Лицензия') }}</h3>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            options="license_type"
                            property="license_type"
                            :label="__('Тип лицензии')" />
                        <form-input
                            :entity="model"
                            property="license_number"
                            :label="__('Номер лицензии')" />
                        <form-input
                            :entity="model"
                            property="license_issued_by"
                            :label="__('Кем выдана')" />
                        <form-date
                            :entity="model"
                            property="license_issued_date"
                            :label="__('Дата выдачи')" />
                        <form-date
                            :entity="model"
                            property="license_expiry_date"
                            :label="__('Дата истечения срока')" />
                    </el-col>
                    <el-col :span="12">
                        <form-date
                            :entity="model"
                            property="license_active_from_date"
                            :label="__('Дата вступления в силу')" />
                        <form-input
                            :entity="model"
                            property="license_order_no"
                            :label="__('Номер приказа')" />
                        <form-text
                            :entity="model"
                            :rows="3"
                            property="license_subject"
                            :label="__('Предмет лицензии')" />
                    </el-col>
                </el-row>
            </section>
        </el-col>
    </el-row>
</template>

<script>
import CONSTANT from '@/constants';

export default {
    props: {
        model: {
            type: Object
        },
    },
    computed: {
        hasAccreditation() {
            return _.isFilled(this.model.accreditation_category)
                &&  this.model.accreditation_category !== CONSTANT.MSP.ACCREDITATION_CATEGORY.NONE;
        }
    },
}
</script>