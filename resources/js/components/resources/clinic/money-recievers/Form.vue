<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Название получателя')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в Актах выполенных работ')" />
                </form-input>
                <form-input
                    :entity="model"
                    property="signer"
                    :label="__('ФИО подписанта')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                </form-input>
                <form-input
                    :entity="model"
                    property="signer_position"
                    :label="__('Должность подписанта')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                </form-input>
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="edrpou"
                    :label="__('Код ЕГРПОУ')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                </form-input>
                <form-input
                    :entity="model"
                    property="bank_account"
                    :label="__('Счет')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                </form-input>
                <form-input
                    :entity="model"
                    property="bank"
                    :label="__('Банк')">
                     <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                </form-input>
            </el-col>
             <el-col :span="24">
                <form-text
                    :entity="model"
                    property="official_additional"
                    :label="__('Реквизиты')">
                    <svg-icon
                        slot="label-addon"
                        name="question-alt"
                        class="icon-tiny"
                        :title="__('Используется в акте выполненых работ')" />
                </form-text>
             </el-col>
        </el-row>
        <div v-if="$can('money-reciever-cashboxes.access')">
            <div class="input-label"><span>Кассы Checkbox</span></div>
            <div class="shrinkable">
                <money-reciever-cashboxes
                    :money-reciever="model" />
            </div>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import MoneyRecieverCashboxes from "./Cashboxes";

export default {
    components: {
        MoneyRecieverCashboxes,
    },
    props: {
        model: {
            type: Object
        },
    },
};
</script>
