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
                        :limit-clinics="$isUpdateLimited('limitations')">
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
                        @save="update" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>
<script>
import LimitationForm from './Form.vue';
import Doctors from './Doctors.vue';
import AppointmentLimitation from '@/models/appointment/limitation';
import FormMixin from './mixins/form';
import WarnExtChanges from '@/mixins/warn-external-changes';

export default {
    mixins: [
        FormMixin,
        WarnExtChanges,
    ],
    components: {
        LimitationForm,
        Doctors,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new AppointmentLimitation({id: this.item.id}),
        }
    },
    mounted() {
        this.model.fetch().then(() => this.setTotalPercents());
    },
    methods: {
        completed() {
            this.$emit('saved', this.model);
        },
        update() {
            this.confirmExternalOverwrite(() => {
                this.saveModel();
            });
        },
    }
}
</script>