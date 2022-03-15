<template>
    <div>
        <p>{{ __('Изменить статус выбранным анализам') }}</p>
        <result-list 
            ref="table" 
            :rows="rows" />
        <div class="pt-20">
            <el-row :gutter="20">
                 <el-col :span="5">
                    <form-select
                        :entity="attributes"
                        :options="statuses"
                        property="status"
                        :label="__('Поставить статус')"
                        :placeholder="__('Статус')"
                        :clearable="true" />
                </el-col>
                <el-col :span="5">
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
        <div class="form-footer text-right">
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
import ResultList from './FormAssignedList.vue';
import Result from '@/models/analysis/result';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    components: {
        ResultList,
    },
    props: {
        rows: Array,
    },
    data() {
        return {
            analysisRows: [...this.rows],
            batchRequest: new BatchRequest('/api/v1/analyses/results/batch'),
            attributes: {
                attachments: [],
                status: null,
            },
            options: {
                type: Object,
                default: () => ({
                    firstDayOfWeek: 1,
                }),
            },
            statuses: this.getStatuses(),
        };
    },
    methods: {
        getStatuses() {
            return this.$handbook.getOptions('analysis_status').filter(option => {
                if (option.id == CONSTANTS.ANALYSIS_RESULT.STATUSES.TEST_IN_OTHER_LABORATORY ||
                    option.id == CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED_BUT_NOT_BE_TEST) {
                    return option;
                }
            });
        },
        applyChanges() {
            this.analysisRows.map(row => {
                if (row.status != CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED) {
                    this.$confirm(__('У анализа «{name}» установлен статус отличный от «Назначены». Вы уверены что хотите его изменить?', {name: row.analysis.name}), () => {
                        row.status = this.attributes.status;
                    });
                } else {
                    row.status = this.attributes.status;
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
                        
                        this.$emit('saved', this.getSelectedList());
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
