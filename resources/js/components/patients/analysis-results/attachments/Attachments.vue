<template>
    <div
        class="tiny-spinner"
        element-loading-spinner="el-icon-loading"
        v-loading="loading">
        <svg-icon
            v-if="attachment && $canManage('analysis-results.update-result', [model.clinic_id])"
            name="edit-alt"
            class="icon-tiny icon-blue"
            :title="__('Редактировать')"
            @click.stop="openForm"/>
        <svg-icon
            v-else-if="$canManage('analysis-results.create-result', [model.clinic_id])"
            name="plus-alt"
            class="icon-tiny icon-blue"
            :title="__('Добавить')"
            @click.stop="openForm"/>
    </div>
</template>
<script>
import AttachmentForm from './Form.vue';
import BlankForm from './Blank.vue';
import FileActionMixin from '@/mixins/file-action';
import AnalysisTemplateRepository from '@/repositories/analysis/template';
import PopupMenu from '@/components/general/PopupMenu.vue';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        model: Object,
    },
    computed: {
        attachment() {
            return (this.model.attachments_data && this.model.attachments_data.length > 0) ? this.model.attachments_data[0] : null;
        },
    },
    data() {
        return {
            loading: false,
        };
    },
    methods: {
        viewFile() {
            if (this.attachment) {
                if (this.isValidType(this.attachment.type)) {
                    return this.view(this.attachment.url, this.attachment.name);
                }
                return this.errorFileFormat();
            }
        },
        downloadFile() {
            if (this.attachment) {
                this.download(this.attachment.url, this.attachment.name);
            }
        },
        openForm() {
            this.checkBlank().then((blank) => {
                if (blank !== false) {
                    if (blank !== null) {
                        this.openBlankForm(blank);
                    } else {
                        this.openAttachmentForm();
                    }
                }
            });
        },
        openBlankForm(blank) {
            this.$modalComponent(BlankForm, {
                template: blank,
                analysis: this.model,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog, data) => {
                    dialog.close();
                    this.updateModel(data);
                },
            }, {
                header: __('Заполнить бланк результатов анализов пациента'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        openAttachmentForm() {
            this.$modalComponent(AttachmentForm, {
                analysis: this.model,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog, data) => {
                    dialog.close();
                    this.updateModel(data);
                },
            }, {
                header: __('Прикрепить результаты анализов пациента'),
                width: '400px',
            });
        },
        updateModel(data) {
            this.model.status = data.status;
            this.model.date_ready = data.date_ready;
            this.model.date_sent_email = data.date_sent_email;
            this.model.delivery_status = data.delivery_status;
            this.model.attachments = data.attachments;
            this.model.attachments_data = data.attachments_data;
            this.model.blank_id = data.blank_id;
            this.model.blank_data = data.blank_data;
        },
        checkBlank() {
            let repository = new AnalysisTemplateRepository();
            this.loading = true;
            return repository.fetch({
                clinic: this.model.clinic_id,
                analysis: this.model.analysis_id,
            }, null, null, 1, 100).then((results) => {
                this.loading = false;

                if (results.rows.length === 0) {
                    return null;
                }

                return new Promise((resolve) => {
                    this.$modalComponent(PopupMenu, {
                        options: results.rows.map(template => ({title: template.name, data: template})),
                        analysis: this.model,
                        attachment: this.attachment,
                    }, {
                        cancel: (dialog) => {
                            dialog.close();
                            resolve(false);
                        },
                        select: (dialog, data) => {
                            dialog.close();
                            resolve(data);
                        },
                        updated: (dialog, data) => {
                            dialog.close();
                            this.updateModel(data);
                        },
                        destroy: (dialog) => {
                            this.model.attachments = [];
                            this.model.save().then(()=> {
                                this.$info(__('Прикреплённый файл успешно удалён'));
                                this.$eventHub.$emit(`resultListRefresh`);
                                dialog.close();
                            });
                        },
                    }, {
                        header: __('Выберите шаблон'),
                        width: '400px',
                    });
                });
            });
        },
    },
}
</script>
