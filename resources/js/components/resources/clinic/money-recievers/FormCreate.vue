<template>
    <money-reciever-form :model="model">
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
    </money-reciever-form>
</template>

<script>
import MoneyReciever from '@/models/clinic/money-reciever';
import MoneyRecieverForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        MoneyRecieverForm,
    },
    data() {
        return {
            model: new MoneyReciever(),
        }
    },
}
</script>
