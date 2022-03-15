<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название')"
                        />
                        <form-select
                            :entity="model"
                            :options="clinics"
                            :multiple="true"
                            :filterable="true"
                            property="clinics"
                            :label="__('Клиника')"
                        />
                        <form-select
                            :entity="model"
                            :options="laboratories"
                            :multiple="true"
                            :filterable="true"
                            property="laboratories"
                            :label="__('Лаборатория')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-upload
                            :multiple="false"
                            :entity="model"
                            property="file_id"
                            :label="__('Шаблон в PDF формате')"
                            :button-text="__('Выберите файл')"
                            accept="application/pdf"
                        />
                        <form-upload
                            :multiple="false"
                            :entity="model"
                            property="stamp_file_id"
                            :label="__('Печать')"
                            :button-text="__('Выберите файл с печатью')"
                            accept="image/jpeg,image/png"
                        />
                        <form-checkbox
                            :entity="filters"
                            property="disabled"
                            class="mt-20"
                            :label="__('Показывать не активные анализы')"
                        />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <form-row name="analyses">
                    <transfer-table
                        v-if="model.loading === false"
                        :fields="fields"
                        :items-fields="itemFields"
                        :items="analyses"
                        v-model="model.analyses"
                        :left-title="__('Анализы')"
                        left-width="320px"
                        :right-title="__('Выбраные анализы')"
                        right-width="320px">
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import AnalysisRepository from '@/repositories/analysis';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        let proxyRepository = new ProxyRepository((opts) => {
            let {filters} = proxyRepository.getFilters(opts.filters);
            if (filters && filters.clinic && filters.clinic.length && 
                filters.laboratory && filters.laboratory.length) {
                let repository = new AnalysisRepository();
                return repository.fetchList(filters);
            } else {
                return Promise.resolve([]);
            }
        });

        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            laboratories: new LaboratoryRepository({
                filters: this.getLaboratoryFilters(),
            }),
            analyses: proxyRepository,
            filters: {
                disabled: false
            },
            itemFields: [
                {
                    name: 'laboratory_name',
                    title: __('Лаборатория'),
                    width: '25%',
                },
            ],
            fields: [
                {
                    name: 'laboratory_name',
                    title: __('Лаборатория'),
                    width: '25%',
                    editable: false,
                },
            ],
        };
    },
    mounted() {
        this.analyses.setFilters(this.getAnalysesFilters());
    },
    methods: {
        getAnalysesFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return _.onlyFilled({
                clinic: this.model.clinics,
                laboratory: this.model.laboratories,
                ...(this.filters.disabled ? {} : {
                    has_price: {
                        from: today,
                        to: today,
                        set: 'base',
                    }
                }),
            });
        },
        getLaboratoryFilters() {
            return _.onlyFilled({
                clinics: this.model.clinics,
            });
        },
    },
    watch: {
        ['model.clinics'](val) {
            this.laboratories.setFilters(this.getLaboratoryFilters());
            this.analyses.setFilters(this.getAnalysesFilters());
        },
        ['model.laboratories'](val) {
            this.analyses.setFilters(this.getAnalysesFilters());
        },
        ['filters.disabled'](val) {
            this.analyses.setFilters(this.getAnalysesFilters());
        },
    },
}
</script>
