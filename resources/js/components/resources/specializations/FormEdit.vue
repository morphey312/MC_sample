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
                            @click.prevent="update">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                </specialization-form>    
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <specialization-clinics 
                    :specialization="model"
                    :modal-component="modalComponent"
                    @cancel="cancel"
                    @specialization-updated="update"
                    @clinics-updated="clinicsUpdated" />
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
import Specialization from '@/models/specialization';
import SpecializationForm from './Form.vue';
import SpecializationClinics from './Clinics.vue';
import Adjacent from './Adjacent.vue';
import CardTemplates from './CardTemplates.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        SpecializationForm,
        SpecializationClinics,
        Adjacent,
        CardTemplates,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: new Specialization({id: this.item.id}),
            activeTab: 'info',
        };
    },
    mounted() {
        this.model.fetch(['clinics', 'additional_templates', 'adjacent_specializations']);
    },
    methods: {
        clinicsUpdated() {
            this.$emit('clinicsUpdated');
        },
    },
}
</script>
