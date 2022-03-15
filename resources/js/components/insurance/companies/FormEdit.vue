<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Данные о компании')"
            name="info">
            <section>
                <company-form :model="model">
                    <div 
                        slot="buttons"
                        class="form-footer text-right">
                        <el-button @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            type="primary"
                            @click="update">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                </company-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <company-clinics 
                    :company="model"
                    :modal-component="modalComponent"
                    @cancel="cancel"
                    @company-updated="update"
                    @clinics-updated="clinicsUpdated" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import CompanyForm from './Form.vue';
import CompanyClinics from './Clinics.vue';
import InsuranceCompany from '@/models/insurance-company';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CompanyForm,
        CompanyClinics,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: new InsuranceCompany({id: this.item.id}),
            activeTab: 'info',
        };
    },
    mounted() {
        this.model.fetch(['price_set']);
    },
    methods: {
        clinicsUpdated() {
            this.$emit('clinicsUpdated');
        },
    },
}
</script>