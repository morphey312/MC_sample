<template>
    <div class="sections-wrapper">
        <template v-if="error.length != 0">
            <alert 
                type="skk"
                icon="warning">
                {{ error }}
            </alert>    
        </template>
        <el-tabs v-model="activeTab" class="tab-group-beige">
            <el-tab-pane
                :lazy="true"
                :label="__('Общие ограничения')"
                name="info">
                <section>
                    <limitation-form 
                        :model="model"
                        :limit-clinics="$isCreationLimited('limitations')">
                        <div 
                            slot="buttons"
                            class="form-footer text-right">
                            <el-button
                                @click="cancel">
                                {{ __('Отменить') }}
                            </el-button>
                            <el-button
                                type="primary"
                                :disabled="invalidPercents || blankPercents"
                                @click="create">
                                {{ __('Добавить') }}
                            </el-button>
                        </div>
                    </limitation-form>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Ограничения для врачей')"
                name="doctors">
                <section>
                    <doctors
                        ref="doctors"
                        :model="model"
                        :invalid-percents="invalidPercents"
                        :blank-percents="blankPercents"
                        @check-percent="checkPercent"
                        @cancel="cancel"
                        @save="create" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>
<script>
import LimitationForm from './Form.vue';
import Doctors from './CreateDoctors.vue';
import AppointmentLimitation from '@/models/appointment/limitation';
import FormMixin from './mixins/form';

export default {
    mixins: [
        FormMixin,
    ],
    components: {
        LimitationForm,
        Doctors,
    },
    data() {
        return {
            model: new AppointmentLimitation(),
        }
    },
    methods: {
        completed() {
            this.$emit('created', this.model);
        },
        create() {
            this.saveModel();
        },
    }
}
</script>