<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Данные о корп. клиенте')"
            name="info">
            <section>
                <entity-form :model="model">
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
                </entity-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <entity-clinics 
                    :legal-entity="model"
                    :modal-component="modalComponent"
                    @cancel="cancel"
                    @legal-entity-updated="update"
                    @clinics-updated="clinicsUpdated" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import EntityForm from './Form.vue';
import EntityClinics from './Clinics.vue';
import LegalEntity from '@/models/legal-entity';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        EntityForm,
        EntityClinics,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: new LegalEntity({id: this.item.id}),
            activeTab: 'info',
        };
    },
    mounted() {
        this.model.fetch();
    },
    methods: {
        clinicsUpdated() {
            this.$emit('clinicsUpdated');
        },
    },
}
</script>