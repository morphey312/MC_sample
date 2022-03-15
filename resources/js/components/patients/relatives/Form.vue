<template>
    <form>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-row
                    name="full_name"
                    :required="true"
                    :label="__('Родственник')">
                    <el-input v-model="model.full_name" :readonly="true" />
                </form-row>
                <form-checkbox
                    :disabled="!model.birthday"
                    :entity="model"
                    :property="model.has_14 ? 'show_in_cabinet_after_14' : 'show_in_cabinet'"
                    :label="model.has_14 ? __('Доступен для просмотра в ЛК (родственник дал согласие на просмотр данных)') : __('Доступен для просмотра в ЛК')"
                    @changed="checkCabinetDeny" />
                <p v-if="model.id && !model.birthday">
                    <svg-icon name="info-alt" class="icon-tiny" />
                    {{ __('Укажите возраст родственника, чтобы иметь возможность включить эту опцию') }}
                </p>
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    options="patient_relatives"
                    property="relation"
                    :label="__('Родственное отношение')"
                />
                <form-upload
                    :entity="model"
                    :multiple="false"
                    property="proving_document_id"
                    :label="__('Документ, подтверждающий право на опеку')" />
            </el-col>
        </el-row>
        <slot name="buttons"/>
    </form>
</template>

<script>
import DenyForm from './DenyForm.vue';

export default {
    props: {
        model: Object,
        patient: Object,
    },
    methods: {
        checkCabinetDeny() {
            this.$nextTick(() => {
                let val = false;
                if (this.model.has_14) {
                    this.model.show_in_cabinet = false;
                    val = this.model.show_in_cabinet_after_14;
                } else {
                    this.model.show_in_cabinet_after_14 = false;
                    val = this.model.show_in_cabinet;
                }
                if (!val) {
                    let prevReason = this.model.cabinet_deny_reason;
                    this.model.cabinet_deny_reason = '';
                    this.$modalComponent(DenyForm, {
                        model: this.model,
                    }, {
                        cancel: (dialog) => {
                            dialog.close();
                            this.model.cabinet_deny_reason = prevReason;
                            if (this.model.has_14) {
                                this.model.show_in_cabinet_after_14 = true;
                                this.model.show_in_cabinet = true;
                            } else {
                                this.model.show_in_cabinet = true;
                            }
                        },
                        done: (dialog) => {
                            dialog.close();
                        },
                    }, {
                        header: __('Укажите причину'),
                        width: '350px',
                        closeOnEscape: false,
                        showClose: false,
                    });
                } else {
                    this.model.cabinet_deny_reason = null;
                }
            });
        }
    },
}
</script>
