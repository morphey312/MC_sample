<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название')" />
                    </el-col>
                    <el-col :span="12">
                        <form-upload
                            :multiple="false"
                            :entity="model"
                            property="file_id"
                            :label="__('Шаблон в PDF формате')"
                            :button-text="__('Выберите файл')"
                            accept="application/pdf" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <form-row name="specializations">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="specializations"
                        v-model="model.specializations"
                        :left-title="__('Специализации')"
                        left-width="280px"
                        :right-title="__('Выбранные специализации')"
                        right-width="280px">
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import SpecializationRepository from '@/repositories/specialization';

export default {
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            specializations: new SpecializationRepository({
                limitClinics: this.limitClinics,
                status: 1
            }),
        };
    },
}
</script>
