<template>
    <clinic-form
        :model="model"
        :modal-component="modalComponent">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
            <el-button
                v-if="$can('clinics.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="updateAndPost">
                {{ __('Сохранить и отправить в e-Health') }}
            </el-button>
        </div>
    </clinic-form>
</template>

<script>
import ClinicForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        EditMixin,
        EhealthMixin,
    ],
    components: {
        ClinicForm,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    mounted() {
        this.model.fetch([
            'medicine_stores',
            'blanks',
            'image',
            'money_reciever',
        ]);
    },
    methods: {
        updateAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.update();
            });
        }
    },
}
</script>
