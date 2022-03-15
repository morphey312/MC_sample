import ResultRepository from '@/repositories/analysis/result';
import MultipleBlankForm from '@/components/patients/analysis-results/attachments/multiple/Blank.vue';
import MultipleAttachmentForm from '@/components/patients/analysis-results/attachments/multiple/Form.vue';
import AnalysisTemplateRepository from '@/repositories/analysis/template';
import PopupMenu from '@/components/general/PopupMenu.vue';
import CONSTANT from '@/constants';

export default {
    methods: {
        sendResults() {
            let selected = this.getSelectedRows();
            let withResults = selected.filter((row) => {
                return row.attachments.length !== 0;
            });

            if (withResults.length === 0) {
                this.$error(__('К выбраным анализам не прикреплены результаты'));
            } else {
                let someUnpaid = withResults.some((r) => !this.isFullyPaidAnalysis(r.appointment_service));
                this.$confirmWhen(someUnpaid, __('Некоторые анализы не оплачены/частично оплачены. Вы уверены, что хотите отправить результаты на e-mail?'), () => {
                    let someSent = withResults.some((r) => r.status === CONSTANT.ANALYSIS_RESULT.STATUSES.EMAIL_SENT);
                    this.$confirmWhen(someSent, __('Некоторые из результатов анализов уже были отправлены, отправить их повторно?'), () => {
                        let repository = new ResultRepository();
                        let resultIds = withResults.map(r => r.id);
                        repository.sendResults(resultIds).then((result) => {
                            let success = 0;
                            withResults.forEach((model) => {
                                if (result[model.id] === true) {
                                    success++;
                                    model.delivery_status = CONSTANT.NOTIFICATION.DELIVERY_STATUS.DELIVERING;
                                    if (model.status !== CONSTANT.ANALYSIS_RESULT.STATUSES.EMAIL_SENT) {
                                        model.status = CONSTANT.ANALYSIS_RESULT.STATUSES.EMAIL_SENT;
                                        model.date_sent_email = this.$moment().format('YYYY-MM-DD');
                                    }
                                }
                            });
                            let errors = withResults.length - success;
                            let message = __('Результаты анализов ({success}) поставлены в очередь на отправку.', {success});
                            if (errors > 0) {
                                this.$warning(__('{message} {errors} результатов не удалось поставить в очередь.', {message, errors}));
                            } else {
                                this.$info(message);
                            }
                        });
                    });
                });
            }
        },
        getSelectedRows() {
            return this.$refs.table.getSelectedRows();
        },
        isFullyPaidAnalysis(analysis) {
            return analysis !== null && analysis.payed >= analysis.cost;
        },
        attachResult() {
            let selected = this.getSelectedRows();
            let withResults = selected.filter((row) => {
                return row.attachments.length !== 0;
            });

            if (withResults.length > 0) {
                if (selected.length === withResults.length) {
                    return this.$error(__('Ко всем выбраным анализам уже прикреплены результаты'));
                }
                return this.$error(__('К некоторым выбраным анализам уже прикреплены результаты'));
            }

            let patients = _.groupBy(selected, 'patient_id');
            if (Object.keys(patients).length > 1) {
                return this.$error(__('Вы не можете привязывать один результат к разным пациентам'));
            }

            let appointments = _.groupBy(selected, 'appointment_id');
            if (Object.keys(appointments).length > 1) {
                return this.$error(__('Вы не можете привязывать один результат к анализам в разных записях'));
            }

            this.checkBlank(selected).then((blank) => {
                if (blank !== false) {
                    if (blank !== null) {
                        if (this.analysisBlankNotAllowed(blank, selected)) {
                            return this.$error(__('Выбранный бланк не может использоваться для одного из анализов'));
                        }
                        this.openBlankForm(blank, selected);
                    } else {
                        this.openAttachmentForm(selected);
                    }
                }
            });
        },
        analysisBlankNotAllowed(blank, selected) {
            let notAllowed = false;
            selected.forEach(item => {
                if (notAllowed) {
                    return;
                }

                if (blank.analyses.indexOf(item.analysis_id) === -1) {
                    notAllowed = true;
                }
            });
            return notAllowed;
        },
        openBlankForm(blank, analyses) {
            this.$modalComponent(MultipleBlankForm, {
                template: blank,
                analyses: analyses,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog, data) => {
                    dialog.close();
                    this.refresh();
                },
            }, {
                header: __('Заполнить бланк результатов анализов пациента'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        openAttachmentForm(analyses) {
            this.$modalComponent(MultipleAttachmentForm, {
                analyses: analyses,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog, data) => {
                    dialog.close();
                    this.refresh();
                },
            }, {
                header: __('Прикрепить результаты анализов пациента'),
                width: '400px',
            });
        },
        checkBlank(selected) {
            console.log(selected);
            let repository = new AnalysisTemplateRepository();
            this.loading = true;
            return repository.fetch({
                clinic: selected[0].clinic_id,
                analysis: selected.map(item => item.analysis_id),
            }, null, null, 1, 100).then((results) => {
                this.loading = false;

                if (results.rows.length === 0) {
                    return null;
                }

                if (results.rows.length === 1) {
                    return results.rows[0];
                }

                return new Promise((resolve) => {
                    this.$modalComponent(PopupMenu, {
                        options: results.rows.map(template => ({title: template.name, data: template})),
                    }, {
                        cancel: (dialog) => {
                            dialog.close();
                            resolve(false);
                        },
                        select: (dialog, data) => {
                            dialog.close();
                            resolve(data);
                        }
                    }, {
                        header: __('Выберите шаблон'),
                        width: '400px',
                    });
                });
            });
        },
    }
};
