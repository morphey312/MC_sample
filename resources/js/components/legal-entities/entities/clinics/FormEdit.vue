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
                @click="update">
                {{ __('Сохранить') }}
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
        item: Object,
        legalEntity: Object,
    },
    data() {
        return {
            model: new EntityClinic({id: this.item.id}),
        };
    },
    mounted() {
        this.model.fetch();
        this.model.setParent(this.legalEntity);
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
