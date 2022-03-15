<template>
    <model-form :model="model">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input-i18n
                        :entity="model"
                        property="name"
                        :label="__('Название статуса')"
                    />
                </el-col>
                <el-col :span="10">
                    <form-row name="color">
                        <label class="input-label">{{ __('Выберите цвет ячейки по умолчанию:') }}</label>
                        <el-color-picker v-model="model.color" />
                    </form-row>
                </el-col>
                <el-col :span="6">
                    <form-checkbox
                        :entity="model"
                        property="for_surgery"
                        :label="__('Для стационара')"
                    />
                </el-col>
            </el-row>
        </section>
        <hr class="grey">
        <section>
            <p><b>{{ __('Укажите свойства статуса:') }}</b></p>
            <el-row :gutter="24">
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="comment_required"
                        :label="__('Указать комментарий при попытке изменить статус')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="patient_card_required"
                        :label="__('Указать карту пациента при записи на прием')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="service_in_order"
                        :label="__('Показывать стоимость услуг в приходном ордере')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="is_active"
                        :label="__('В специализации может быть только одна первичная запись пациента')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="sms_for_card"
                        :label="__('Формировать SMS с номером карты пациента')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="service_in_cost"
                        :label="__('Услуги и анализы идут в долг пациенту при записи к врачу')"
                    />
                </el-col>
            </el-row>
            <slot name="buttons"></slot>
        </section>
    </model-form>
</template>

<script>
    export default {
        props: {
            model: Object
        },
    }
</script>