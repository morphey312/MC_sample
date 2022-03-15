<template>
    <form-row
        v-loading="loading"
        :name="property"
        :label="label"
        :required="isRequired"
        :css-class="cssClass"
        :error-prefix="errorPrefix">
        <template
            v-if="$slots['label-addon']"
            slot="label-addon">
            <slot name="label-addon" />
        </template>
        <el-upload
            ref="upload"
            :class="['upload-attachment', {'not-empty': hasAttachments, multiple: multiple}]"
            :action="''"
            :disabled="disabled"
            :file-list="fileList"
            :limit="multiple ? limit : 1"
            :accept="accept"
            :multiple="multiple"
            :on-preview="viewFile"
            :http-request="xhr"
            :before-upload="uploadStart"
            :on-success="uploadComplete"
            :on-error="uploadError"
            :before-remove="uploadDiscard"
            :on-remove="fileRemoved">
            <el-button type="primary" v-show="showButton">{{ buttonText }}</el-button>
        </el-upload>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';
import FileAttachmentRepository from '@/repositories/file-attachment';
import FileActionMixin from '@/mixins/file-action';

function upload(options) {
    let request = this.fileLoader.upload(options.file, options.file.name, {
        onUploadProgress: (event) => {
            if (event.total > 0) {
                event.percent = event.loaded / event.total * 100;
            } else {
                event.percent = 0;
            }
            options.onProgress(event);
        }
    });

    request.promise().then((response) => {
        options.onSuccess(response.data);
    }).catch((error) => {
        options.onError(error);
    });

    return request;
}

export default {
    mixins: [
        FormElement,
        FileActionMixin,
    ],
    props: {
        buttonText: {
            type: String,
            default: __('Прикрепить файлы'),
        },
        limit: {
            type: Number,
            default: 100,
        },
        accept: {
            type: String,
            default: '',
        },
        multiple: {
            type: Boolean,
            default: true,
        },
        onPreview: {
            type: [Function, Boolean],
            default: true,
        },
    },
    computed: {
        xhr() {
            return upload.bind(this);
        },
        showButton() {
            return this.multiple
                ? this.model.length < this.limit
                : this.model === null;
        },
        hasAttachments() {
            return this.multiple
                ? this.model.length !== 0
                : this.model !== null;
        },
    },
    data() {
        return {
            loading: false,
            fileList: [],
            countUploads: 0,
        };
    },
    mounted() {
        if (this.multiple) {
            if (this.model.length !== 0) {
                this.loadFiles(this.model);
            }
        } else if (this.model !== null) {
            this.loadFiles(this.model);
        }
    },
    methods: {
        uploadStart() {
            this.countUploads++;
            this.$emit('uploads-started', this.countUploads);
        },
        uploadComplete(response, file, fileList) {
            if (this.multiple) {
                this.model.push(response.id);
            } else {
                this.model = response.id;
            }
            this.countUploads--;
            this.$emit('uploads-completed', this.countUploads);
        },
        uploadError(error) {
            if (!axios.isCancel(error)) {
                this.$error(__('Не удалось загрузить файл'));
            }
            this.countUploads--;
            this.$emit('uploads-completed', this.countUploads);
        },
        uploadDiscard(file, fileList) {
            if (file.status === 'uploading') {
                this.$refs.upload.abort(file);
                return false;
            }
        },
        fileRemoved(file, fileList) {
            if (file.response) {
                if (this.multiple) {
                    this.model = _.without(this.model, file.response.id);
                } else {
                    this.model = null;
                }
            }
            this.$emit('file-removed');
        },
        loadFiles(ids) {
            let repository = new FileAttachmentRepository();
            this.loading = true;
            repository.fetch({id: ids}).then((result) => {
                result.rows.forEach((row) => {
                    this.fileList.push({
                        name: row.name,
                        url: row.url,
                        response: row,
                    });
                });
                this.loading = false;
            });
        },
        viewFile(file) {
            if (this.onPreview === true) {
                this.view(file.response.url, file.name);
            } else if (typeof this.onPreview === 'function') {
                this.onPreview(file);
            }
        }
    },
};
</script>
