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
                            v-if="model.isNew()"
                            type="primary"
                            @click="create">
                            {{ __('Далее') }}
                        </el-button>
                    </div>
                </company-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="model.isNew()"
            :label="__('Клиники компании')"
            name="clinics">
            <section>
                <company-clinics 
                    :company="model"
                    @cancel="cancel"
                    @completed="completed" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import InsuranceCompany from '@/models/insurance-company';
import CompanyForm from './Form.vue';
import CompanyClinics from './CreateClinics.vue';

export default {
    components: {
        CompanyForm,
        CompanyClinics,
    },
    data() {
        return {
            model: new InsuranceCompany(),
            activeTab: 'info',
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Данные компании успешно сохранены'));
                this.activeTab = 'clinics';
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        completed() {
            this.$emit('created', this.model);
        },
    },
}
</script>