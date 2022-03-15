<template>
    <div>
        <reason-form :model="model">
            <div 
                slot="buttons"
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
        </reason-form>
    </div>
</template>

<script>
import DelayReason from '@/models/appointment/status/delay-reason';
import ReasonForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ReasonForm,
    },
    data() {
        return {
            model: new DelayReason(),
        };
    },
}
</script>