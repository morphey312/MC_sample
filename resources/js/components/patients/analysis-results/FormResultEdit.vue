<template>
    <div>
        <result-list
            ref="table"
            :rows="rows" />
        <div class="pt-20">
            <el-row :gutter="20">
                <el-col v-if="canSetDateReady" :span="5" >
                    <form-date
                        :entity="attributes"
                        property="date_ready"
                        :label="__('Поставить дату готовности')"
                        :placeholder="__('Дата готовности')"
                        :clearable="true"
                        :editable="true" />
                </el-col>
                <el-col v-if="canSetDateEmailSent" :span="5">
                    <form-date
                        :entity="attributes"
                        property="date_sent_email"
                        :label="__('Поставить дату отправки на e-mail')"
                        :placeholder="__('Дата отправки на e-mail')"
                        :clearable="true"
                        :editable="true" />
                </el-col>
                <el-col v-if="canUploadResult" :span="5">
                    <form-upload
                        ref="attachments"
                        :entity="attributes"
                        :limit="1"
                        property="attachments"
                        :label="__('Прикрепить результаты анализов')" />
                </el-col>
                <el-col :span="5">
                    <div class="label-wrapper">&nbsp;</div>
                    <el-button
                        @click="applyChanges">
                        {{ __('Применить к выбранным') }}
                    </el-button>
                </el-col>
            </el-row>
        </div>
        <div class="form-footer text-right mt-0">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="saveChanges">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ResultList from './FormEditList.vue';
import Result from '@/models/analysis/result';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    components: {
        ResultList,
    },
    props: {
        rows: Array,
        canSetDateReady: {
            type: Boolean,
            default: true
        },
        canSetDateEmailSent: {
            type: Boolean,
            default: true
        },
        canUploadResult: {
            type: Boolean,
            default: true
        },
    },
    data() {
        return {
            analysisRows: [...this.rows],
            batchRequest: new BatchRequest('/api/v1/analyses/results/batch'),
            attributes: {
                date_ready: this.$moment().format('YYYY-MM-DD'),
                date_sent_email: null,
                attachments: [],
            },
            options: {
                type: Object,
                default: () => ({
                    firstDayOfWeek: 1,
                }),
            },
        };
    },
    methods: {
        applyChanges() {
            this.analysisRows.map(row => {
                if (_.isVoid(row.date_ready)) {
                    row.date_ready = this.attributes.date_ready;
                } else {
                    this.$confirm(__('У анализа «{name}» установлена дата готовности. Вы уверены что хотите ее изменить?', {name: row.analysis.name}), () => {
                        row.date_ready = this.attributes.date_ready;
                    });
                }

                if (this.attributes.date_sent_email !== null) {
                    if (_.isVoid(row.date_sent_email)) {
                        row.date_sent_email = this.attributes.date_sent_email;
                    } else {
                        this.$confirm(__('У анализа «{name}» установлена дата отправки на e-mail. Вы уверены что хотите ее изменить?', {name: row.analysis.name}), () => {
                            row.date_sent_email = this.attributes.date_sent_email;
                        });
                    }

                }

                if (this.attributes.attachments.length != 0) {
                    if (row.attachments.length == 0) {
                        row.attachments = this.attributes.attachments;
                    } else {
                        this.$confirm(__('У анализа «{name}» уже прикреплен результат. Вы уверены что хотите прикрепить новый?', {name: row.analysis.name}), () => {
                            row.attachments = this.attributes.attachments;
                        });
                    }
                }
                return row;
            });
        },
        saveChanges() {
            let changed = this.getChangedRows();

            if (changed.length !== 0) {
                this.batchRequest.reset();

                let notYetSubmitted = [];
                changed.forEach(item => {
                    this.batchRequest.update(item);
                    if (!this.isSubmittedStatus(item.status)) {
                        notYetSubmitted.push(item);
                    }
                });
                
                return this.batchRequest.submit().then((result) => {
                    if (result.failure.length !== 0) {
                        this.$error(__('Не удалось сохранить некоторые данные'));
                    } else {
                        let nowSubmitted = 0;
                        notYetSubmitted.forEach((item) => {
                            if (this.isSubmittedStatus(item.status)) {
                                nowSubmitted++;
                            }
                        });
                        
                        if (nowSubmitted !== 0) {
                            setTimeout(() => {
                                this.$info(__('{submitted} результатов анализов были поставлены в очередь на отправку', {submitted: nowSubmitted}));
                            }, 500);
                        }

                        return this.$emit('saved', this.getSelectedList());
                    }
                }).catch((error) => {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                });
            }
            return this.confirmCancel();
        },
        getSelectedList() {
            return this.analysisRows.map(row => row.id);
        },
        confirmCancel() {
            return this.$confirm(__('Ни один анализ не был изменен. Закрыть форму редактирования?'), () => this.cancel());
        },
        getChangedRows() {
            let changed = [];
            this.analysisRows.forEach((analysis) => {
                if (this.isRowChanged(analysis)) {
                    changed.push(analysis);
                }
            });
            return changed;
        },
        isRowChanged(analysis) {
            return analysis.changed() !== false;
        },
        isSubmittedStatus(status) {
            return status === CONSTANTS.ANALYSIS_RESULT.STATUSES.EMAIL_SENT;
        },
        cancel() {
            this.$emit('cancel');
        },
    },
}
</script>
