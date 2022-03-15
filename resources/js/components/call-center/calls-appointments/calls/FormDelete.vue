<template>
    <model-form :model="model">
        <form-select
            :entity="model"
            :options="reasons"
            property="call_delete_reason_id"
            :label="__('Причина')"
        />
        <form-text
            :entity="model"
            property="delete_reason_comment"
            :label="__('Примечание к удалению')"
        />
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="deleteCall">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
import CallDeleteReasonRepository from '@/repositories/calls/delete-reason';

export default {
    props: {
        item: Object,
    },
    data() {
        return {
            reasons: new CallDeleteReasonRepository({filters: {useForDelete: 0}}),
            model: this.item.clone(),
        };
    },
    mounted() {
        this.model.delete_reason = true;
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        deleteCall() {
            this.model.save().then((response) => {
                this.$info(__('Звонок успешно удален'));
                this.$emit('deleted');
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        setCommentRequired(id) {
            let reason = _.findById(this.reasons, id);
            if (reason && reason.comment_required) {
                this.model.comment_required = true;
            } else {
                this.model.unset(['comment_required']);
            }
        },
    },
    watch: {
        ['model.call_delete_reason_id'](val) {
            this.setCommentRequired(val);
        },
    },
}
</script>