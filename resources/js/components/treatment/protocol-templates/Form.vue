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
                            :options="specializations"
                            :filterable="true"
                            property="specialization_id"
                            :label="__('Специализация')"
                        />
                        <form-checkbox
                            :entity="filters"
                            property="disabled"
                            :label="__('Показывать не активные услуги')"
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
                        <form-select
                            :entity="model"
                            :options="clinics"
                            :multiple="true"
                            :filterable="true"
                            property="clinics"
                            :label="__('Клиника')"
                        />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <form-row name="services">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="services"
                        v-model="model.services"
                        :left-title="__('Услуги')"
                        left-width="280px"
                        :right-title="__('Выбраные услуги')"
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
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
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
        let proxyRepository = new ProxyRepository((filters) => {
            let query = proxyRepository.getFilters(filters);
            if (query.filters && query.filters.specialization && query.filters.clinic.length) {
                let repository = new ServiceRepository();
                return repository.fetchList(query.filters);
            } else {
                return Promise.resolve([]);
            }
        }, {
            filters: this.getServicesFilters(),
        });

        return {
            specializations: new SpecializationRepository({
                limitClinics: this.limitClinics,
            }),
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
                filters: this.getClinicsFilters(),
            }),
            services: proxyRepository,
            filters: {
                disabled: false
            },
            today: this.$moment().format('YYYY-MM-DD')
        };
    },
    methods: {
        getClinicsFilters() {
            return _.onlyFilled({
                has_specialization: this.model.specialization_id,
            });
        },
        getServicesFilters() {
            return _.onlyFilled({
                specialization: this.model.specialization_id,
                clinic: this.model.clinics,
                disabled: false,
                has_price : {
                    from: this.today,
                    to: this.today,
                    set: 'base'
                }
            });
        },
    },
    watch: {
        ['model.specialization_id'](val) {
            this.clinics.setFilters(this.getClinicsFilters());
            this.services.setFilters(this.getServicesFilters());
        },
        ['model.clinics'](val) {
            this.services.setFilters(this.getServicesFilters());
        },
        ['filters.disabled'](val){
            let filters = this.getServicesFilters();

            if (val) {
                delete filters.has_price;

                filters.has_no_price = {
                    from: this.today,
                    to: this.today,
                    set: 'base'
                };
            }
            this.services.setFilters(filters);
        }
    },
}
</script>
