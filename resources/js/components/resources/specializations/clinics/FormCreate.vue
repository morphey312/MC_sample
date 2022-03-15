<template>
	<clinic-form :model="model">
        <div slot="buttons"
            class="form-footer text-right">
        	<el-button @click="cancel">
                {{ __('Отменить') }}
           	</el-button>
           	<el-button
                type="primary"
                @click.prevent="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </clinic-form>
</template>
<script>
import SpecializationClinic from '@/models/specialization/clinic';
import ClinicForm from './Form.vue';

export default {
	components: {
        ClinicForm
    },
    props: {
        specialization: Object,
    },
    data() {
        return {
            model: new SpecializationClinic({
                specialization_id: this.specialization.id,
            }),
        }
    },
    mounted() {
        this.model.setParent(this.specialization);
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