<template>
    <clinic-group-form :model="model">
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </clinic-group-form>
</template>

<script>
import ClinicGroup from '@/models/clinic/group';
import ClinicGroupForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ClinicGroupForm,
    },
    data() {
        return {
            model: new ClinicGroup(),
        }
    },
}
</script>
