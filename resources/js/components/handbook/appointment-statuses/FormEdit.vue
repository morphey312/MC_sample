<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
         <el-tab-pane
            :lazy="true"
            :label="__('Общие')"
            name="info">
            <status-form :model="model">
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
            </status-form>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Причины изменения статуса')"
            name="reasons">
            <reason-form :model="model" >
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
            </reason-form>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Причины задержки изменения статуса')"
            name="delay-reasons">
            <delay-reason-form :model="model" >
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
            </delay-reason-form>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import StatusForm from './Form.vue';
import ReasonForm from './ReasonForm.vue';
import DelayReasonForm from './DelayReasonForm.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        StatusForm,
        ReasonForm,
        DelayReasonForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
            activeTab: 'info',
        }
    },
}
</script>