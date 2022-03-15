<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <b v-html="information" />
            </section>
            <hr>
            <section>
                <form-text
                    :entity="model"
                    property="comment_canceled"
                    :label="__('Примечание к отмене')"
                />
            </section>
        </div>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="cancelCallRequest">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
import CONSTANTS from '@/constants';

export default {
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
            information: '',
        };
    },
    mounted() {
        this.model.fetch().then(() => {
            this.information = this.makeInformation(this.model.attributes);
            if (this.model.status != CONSTANTS.CALL_REQUEST.STATUS.CANCELLED) {
                this.model.original_status = this.model.status;
                this.model.status = CONSTANTS.CALL_REQUEST.STATUS.CANCELLED;
            }
        });
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        cancelCallRequest() {
            this.model.save().then((response) => {
                this.$emit('canceled');
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        makeInformation(data) {
            return [
                __('Заявка на прозвон создана оператором {name}.', {name: data.operator_name}),
                __('Время создания заявки {date}.', {date: this.$formatter.datetimeFormat(data.created_at)}),
                __('Заявка создана {added}.', {added: this.$handbook.getOption('call_request_added', data.added)}),
                __('Статус ‒ {status}.', {status: this.$handbook.getOption('call_request_status', data.status)}),
                __('Цель прозвона ‒ {purpose}.', {purpose: data.call_request_purpose}),
            ].join(' ');
        }
    }
}
</script>