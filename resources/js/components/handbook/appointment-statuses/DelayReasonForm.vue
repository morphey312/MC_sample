<template>
	<section>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="has_delay"
                    class="white-space-wrap"
                    :label="__('Указать причину задержки изменения статуса')"
                />
            </el-col>
            <el-col :span="12">
                <form-input-number
                    :entity="model"
                    property="delay"
                    :label="__('Кол-во минут, после начала приема врача')"
                />
            </el-col>
        </el-row>
        <form-row name="delay-reasons" v-if="model.has_delay == true">
            <transfer-table
                v-if="model.loading === false"
                key="reasons"
                :items="delayReasons"
                v-model="model.delay_reasons"
                :left-title="__('Все причины')"
                left-width="300px"
                :right-title="__('Выбранные причины')"
                right-width="300px"
                :emptySelectionMessage="placeholder">
            </transfer-table>
        </form-row>
        <slot name="buttons"></slot>
    </section>
</template>

<script>
import DelayReasonRepository from '@/repositories/appointment/status/delay-reason';

export default {
	props: {
		model: Object
	},
	data() {
        return {
            delayReasons: [],
            placeholder: this.getPlaceholder(),
        }
    },
    mounted() {
        this.getReasons();
    },
    methods: {
        getReasons() {
            let reason = new DelayReasonRepository();
            reason.fetchList().then((response) => {
                this.delayReasons = response;
            });
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Выберите и добавьте причины слева'));
        },
    }
}	
</script>