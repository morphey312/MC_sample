<template>
	<div>
		<el-row :gutter="20">
            <el-col :span="24">
				<form-select
					:required="true"
		            :entity="model"
		            :options="reasons"
                    :filterable="true"
		            property="status_reason_id"
		            :label="__('Выберите причину изменения статуса')"
		        />
		        <form-text
		        	:required="commentRequired"
		            :entity="model"
		            property="status_reason_comment"
		            :label="__('Комментарий к причине изменения статуса')"
		        />
		    </el-col>
		</el-row>
		<el-row class="text-right dialog-footer">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="updateReason">
                {{ __('Сохранить') }}
            </el-button>
        </el-row>
	</div>
</template>
<script>
import ReasonRepository from '@/repositories/appointment/status/reason';

export default {
	props: {
		model: Object,
		status: Object,
	},
	data() {
		return {
			reasons: new ReasonRepository(),
			commentRequired: this.status.comment_required,
        }
	},
	mounted() {
		this.setDefaultReason();
	},
	methods: {
		setDefaultReason() {
			if(this.status.reasons.length == 0) {
				return;
			}

			let reason = _.find(this.status.reasons, {default: true});

			if(reason) {
				this.model.status_reason_id = reason.id;
			}
		},
		cancel() {
			this.model.unset([
                'status_reason_id', 
                'status_reason_comment', 
            ]);
            this.$emit('cancel');
		},
		updateReason() {
			if(_.isVoid(this.model.status_reason_id)) {
				return this.$error(__('Выберите причину изменения статуса'));
			}

			if(this.commentRequired && _.isVoid(this.model.status_reason_comment)) {
				return this.$error(__('Укажите комментарий к причине изменения статуса'));
			}

			this.$emit('reason_updated');
		},
	},
}	
</script>
