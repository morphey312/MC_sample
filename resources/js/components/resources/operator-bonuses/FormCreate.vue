<template>
    <norm-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </norm-form>
</template>

<script>
import NormForm from './Form.vue';
import BonusNorm from '@/models/clinic/bonus-norm';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        NormForm,
    },
    data() {
        return {
            model: new BonusNorm(),
        }
    },
}
</script>