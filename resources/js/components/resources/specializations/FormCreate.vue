<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Общие')"
            name="info">
            <section>
                <specialization-form :model="model">
                    <div 
                        slot="buttons"
                        class="form-footer text-right">
                        <el-button @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            type="primary"
                            @click.prevent="create">
                            {{ __('Далее') }}
                        </el-button>
                    </div>
                </specialization-form>    
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="model.isNew()"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <specialization-clinics 
                    :specialization="model"
                    @cancel="cancel"
                    @clinics-changed="addClinics"
                    @completed="completed" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Смежные специализации')"
            name="specializations">
            <adjacent 
                :model="model"
                @cancel="cancel"
                @specialization-updated="update" />
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Доп. шаблоны карты')"
            name="card_templates">
            <card-templates 
                :model="model"
                @cancel="cancel"
                @specialization-updated="update" />
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import SpecializationForm from './Form.vue';
import SpecializationClinics from './CreateClinics.vue';
import Specialization from '@/models/specialization';
import CardTemplates from './CardTemplates.vue';
import Adjacent from './Adjacent.vue';

export default {
    components: {
        SpecializationForm,
        SpecializationClinics,
        Adjacent,
        CardTemplates,
    },
    data() {
        return {
            model: new Specialization(),
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
                this.$info(__('Специализация была успешно создана'));
                this.activeTab = 'clinics';
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        update() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        completed() {
            this.$emit('created', this.model);
        },
        addClinics(clinics) {
            this.model.clinics = clinics;
        },
    },
}
</script>
