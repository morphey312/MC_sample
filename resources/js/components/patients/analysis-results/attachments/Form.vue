<template>
    <div>
        <warning 
            :was-submitted="isSubmittedStatus(model.status)"
            :fully-paid="fullyPaid"
            :has-email="hasEmail"
            :auto-email="autoEmail" />
        <form-upload
            ref="attachments"
            :entity="model"
            :limit="1"
            property="attachments" />
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import AttachmentMixin from '../mixins/attachment';

export default {
    mixins: [
        AttachmentMixin,
    ],
    data() {
        return {
            fileList: [],
        }
    },
    mounted() {
        this.fileList = this.model._attributes.attachments_data;
    },
    computed: {
        attachment() {
            return (this.model.attachments.length !== 0) ? this.fileList[0] : null;
        },
        hasAttachments() {
            return this.fileList.length !== 0;
        },
    },
    watch: {
        ['model.attachments']() {
            this.fileList = this.fileList.filter((file) => {
                return this.model.attachments.indexOf(file.id) !== -1;
            });
        },
    },
}   
</script>