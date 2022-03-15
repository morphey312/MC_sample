<template>
    <div class="sections-wrapper">
        <section class="popup-menu">
            <div
                v-for="(option, index) in options"
                :key="index"
                class="el-dropdown-menu__item"
                @click="select(option)">
                {{ option.title }}
            </div>
        </section>
        <section class="popup-menu">
            <form-upload
                ref="attachments"
                :entity="model"
                :limit="1"
                property="attachments" />
        </section>
        <div class="dialog-footer text-right">
            <el-button
                v-if="canDestroy"
                @click="destroy"
                type="danger">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                @click="cancel">
                {{ __('Отмена') }}
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
import AttachmentMixin from "@/components/patients/analysis-results/mixins/attachment";

export default {
    mixins: [
        AttachmentMixin,
    ],
    props: {
        options: Array,
        attachment: Object
    },
    methods: {
        select(option) {
            this.$emit('select', option.data);
        },
        cancel() {
            this.$emit('cancel');
        },
        destroy() {
            this.$emit('destroy');
        },
    },
    computed: {
        canDestroy() {
            return this.attachment && this.$can('analysis-results.delete-result');
        }
    }
}
</script>
