<template>
    <destination-form :model="model">
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
    </destination-form>
</template>

<script>
import DestinationForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        DestinationForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        }
    },
}
</script>