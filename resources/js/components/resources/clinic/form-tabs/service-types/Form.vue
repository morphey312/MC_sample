<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            property="speciality_type_id"
                            :options="specialities"
                            :disabled="serviceCommited"
                            :label="__('Специализация')" />
                        <form-select
                            :entity="model"
                            property="providing_condition"
                            :disabled="serviceCommited"
                            options="service_providing_conditions"
                            :label="__('Условия предоставления')" />
                    </el-col>
                    <el-col :span="8">
                        <schedule
                            :label="__('График доступности')"
                            :breaks="false"
                            v-model="model.available_time" />
                        <form-select
                            :entity="model"
                            options="active_status"
                            property="is_active"
                            :label="__('Статус')" />
                    </el-col>
                    <el-col :span="8">
                        <form-text
                            :entity="model"
                            property="comment"
                            :label="__('Комментарий')" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <h3>{{ __('Услуги недоступны') }}</h3>
                <unavailable :model="model" />
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import EmployeeSpecialityTypeRepository from '@/repositories/employee/speciality-type';
import Schedule from '@/components/general/form/Schedule.vue';
import Unavailable from './unavailable/Unavailable.vue';

export default {
    components: {
        Schedule,
        Unavailable,
    },
    props: {
        model: Object,
    },
    computed: {
        serviceCommited() {
            return _.isFilled(this.model.ehealth_id);
        }
    },
    data() {
        return {
            specialities: new EmployeeSpecialityTypeRepository(),
        }
    },
}
</script>