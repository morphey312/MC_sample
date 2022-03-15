<template>
    <div>
        <warning 
            :was-submitted="hasSubmittedAnalysis"
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
import AttachmentMixin from './mixins/attachment';

export default {
    mixins: [
        AttachmentMixin,
    ],
}   
</script>