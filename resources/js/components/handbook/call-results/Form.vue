<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <form-input-i18n
                    :entity="model"
                    property="name"
                    :label="__('Название результата звонка')" />
            </section>
            <hr />
            <section>
                <div class="form-input"><b>{{ __('Результат звонка используется для:') }}</b></div>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-checkbox
                            :entity="model"
                            property="use_for_new_calls"
                            :label="__('Новых контактов')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="use_for_statistics"
                            :label="__('Статистики')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="use_for_is_first_patient"
                            :label="__('Первичных пациентов')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="use_for_repeated_patient"
                            :label="__('Повторных пациентов')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-checkbox
                            :entity="model"
                            property="use_for_unspecialized_patient"
                            :label="__('Непрофильных пациентов')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="use_for_not_patient"
                            :label="__('Не пациентов')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="esputnik_no_answer"
                            :label="__('Отслеживания недозвонов в eSputnik')"
                        />
                        <form-checkbox
                            :entity="model"
                            property="for_wait_list"
                            :label="__('Листа ожидания')"
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
        model: Object
    }
}
</script>
