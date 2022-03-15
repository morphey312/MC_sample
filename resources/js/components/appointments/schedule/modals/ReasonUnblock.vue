<template>
    <div class="sections-wrapper">
        <section class="grey">
            <el-row :gutter="20">

                <el-col :span="24">
                    <form-select
                        :entity="doctorUnblock"
                        :options="reasons"
                        property="reason"
                        :filterable="true"
                        :label="__('Причина разблокировки')" />


                </el-col>
                <el-col :span="24">
                    <form-text
                        :entity="doctorUnblock"
                        property="comment"
                        :rows="2"
                        class="three-rows-height"
                        :label="__('Комментарий')" />


                </el-col>

            </el-row>
            <div
                class="form-footer text-right">
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="unblock()">
                    {{ __('Разблокировать') }}
                </el-button>
            </div>
        </section>
    </div>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ReasonUnblockRepository from '@/repositories/reason-unblock';
import CONSTANTS from '@/constants';
import MESSAGES from '@/messages';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {

        item: {
            type: Object
        },
        unlockIndex: {
            type: Number
        },
        columnDay: {
            type: Object
        },
    },
    data() {
        return {
            reasons: new ReasonUnblockRepository(),
            doctorUnblock: {}
        };
    },
    mounted() {
        this.doctorUnblock = {
            reason: null,
            comment: null
        }
    },

    methods: {
        cancel() {
            this.$emit('close');
        },
        unblock(){
            if(this.doctorUnblock.reason == null || this.doctorUnblock.comment == null){
                this.$info(__('Укажите причину разблокирования и комментарий'));
            }else{
                this.$emit('unblock', this.doctorUnblock);
            }
        },



    }
}
</script>
