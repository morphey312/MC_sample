<template>
	<section>
        <el-row :gutter="20">
            <el-col :span="8">
                <form-checkbox
                    :entity="model"
                    property="status_reason"
                    class="white-space-wrap"
                    :label="__('Указать причину изменения статуса')"
                />
            </el-col>
        </el-row>
        <form-row name="status-reasons" v-if="model.status_reason == true">
            <transfer-table
                v-if="model.loading === false"
                key="reasons"
                :items="reasons"
                v-model="model.reasons"
                :fields="reasonFields"
                :left-title="__('Все причины')"
                left-width="300px"
                :right-title="__('Выбранные причины')"
                right-width="215px"
                :emptySelectionMessage="placeholder"
                @new-row="initReason">
                <template slot="default" slot-scope="props">
                    <form-checkbox
                        :entity="props.rowData.data"
                        property="default"
                        css-class="table-row"
                        @changed="toggleReasonDefault(props.rowData.data, $event)" />
                </template>
            </transfer-table>
        </form-row>
        <slot name="buttons"></slot>
    </section>
</template>

<script>
import ReasonRepository from '@/repositories/appointment/status/reason';

export default {
	props: {
		model: Object
	},
	data() {
        return {
            reasons: [],
            placeholder: this.getPlaceholder(),
            reasonFields: [
                {
                    name: 'default',
                    title: __('Причина по умолчанию'),
                    width: '100px',
                },
            ],
        }
    },
    mounted() {
        this.getLists();
    },
    methods: {
        getLists() {
            let reason = new ReasonRepository();
            reason.fetchList().then((response) => {
                this.reasons = response;
            });
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Выберите и добавьте причины слева'));
        },
        initReason(data) {
            data.default = false;
        },
        toggleReasonDefault(row, value) {
            if (value == true) {
                this.model.reasons.forEach((reason) => {
                    if (row.id != reason.id) {
                        reason.default = false;
                    }
                });
            }
        },
    }
}	
</script>