<template>
    <service-form 
        :model="model"
        :limit-clinics="$isCreationLimited('services')">
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
                {{ __('Создать') }}
            </el-button>
        </div>
    </service-form>
</template>

<script>
import ServiceForm from './Form.vue';
import Service from '@/models/service';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ServiceForm,
    },
    data() {
        return {
            model: new Service(),
        }
    },
}
</script>