<template>
	<model-form :model="model">
		<el-row :gutter="20">
			<el-col :span="12">
                <form-input
                    :entity="model"
                    property="name"
                />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-upload
                    ref="attachments"
                    :entity="model"
                    :limit="1"
                    accept="image/jpeg,image/png"
                    property="attachments"
                    @uploads-completed="setAttachment"
                    @file-removed="setAttachment"
                />
            </el-col>
            <el-col :span="12" class="text-center">
                <img 
                    v-if="src"
                    :src="src"
                    style="height: 40px" />
            </el-col>
        </el-row>
        <slot name="buttons" />
	</model-form>
</template>

<script>
import LoadImageMixin from './mixin/load-image';

export default {
    mixins: [
        LoadImageMixin,
    ],
	props: {
		model: Object,
	},
    data() {
        return {
            attachment: null,
        }
    },
    mounted() {
        this.initAttachment();
        if (this.attachment) {
            this.getImage();
        }
    },
    methods: {
        getAttachments() {
            return this.$refs.attachments.$refs.upload.uploadFiles;
        },
        setAttachment() {
            let attachments = this.getAttachments();
            if (attachments.length !== 0) {
                this.attachment = attachments[0].response;
                this.getImage();
            } else {
                this.attachment = null;
                this.src = null;
            }
        },
        initAttachment() {
            if (this.model.attachments.length !== 0 && this.model.attachments_data.length !== 0) {
                this.attachment = this.model.attachments_data[0];    
            }
        },
    },
}	
</script>