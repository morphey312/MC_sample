<template>
	<div v-loading="model.saving">
		<el-row :gutter="20">
            <el-col :span="24">
				<form-select
					:required="true"
		            :entity="model"
		            :options="delayReasons"
                    :filterable="true"
		            property="delay_reason_id"
		            :label="__('Выберите причину изменения статуса')"
		        />
		        <form-text
		            :entity="model"
		            property="comment"
		            :label="__('Комментарий к причине изменения статуса')"
		        />
		    </el-col>
		</el-row>
		<el-row class="text-right dialog-footer">
            <el-button
                type="primary"
                @click.prevent="create">
                {{ __('Сохранить') }}
            </el-button>
        </el-row>
	</div>
</template>
<script>
import AppointmentDelay from '@/models/appointment/delay';

export default {
	props: {
		appointment: Object,
        status: Object,
        duration: {
            type: Number,
            default: 0,
        },
	},
	data() {
		return {
            model: new AppointmentDelay({
                duration: this.duration,
                appointment_id: this.appointment.id,
            }),
			delayReasons: this.status.delay_reasons,
        }
	},

	methods: {
        create() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.$info(__('Причина задержки добавлена'));
                    this.$emit('created', this.model);
                }
            });
        },
	},
}
</script>
