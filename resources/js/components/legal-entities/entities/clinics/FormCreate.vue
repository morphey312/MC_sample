<template>
    <clinic-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </clinic-form>    
</template>

<script>
import EntityClinic from '@/models/legal-entity/clinic';
import ClinicForm from './Form.vue';

export default {
    components: {
        ClinicForm
    },
    props: {
        legalEntity: Object,
    },
    data() {
        return {
            model: new EntityClinic({
                legal_entity_id: this.legalEntity.id,
            }),
        }
    },
    mounted() {
        this.model.setParent(this.legalEntity);
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
