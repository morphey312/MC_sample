<template>
    <service-form 
        :model="model"
        :limit-clinics="$isUpdateLimited('services')">
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
    </service-form>
</template>

<script>
import ServiceForm from './Form.vue';
import Service from '@/models/service';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ServiceForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new Service(),
        }
    },
    mounted() {
        this.model.set('id', this.item.id);
        this.model.fetch();
    },
}
</script>