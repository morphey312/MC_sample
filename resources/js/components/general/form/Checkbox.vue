<template>
    <form-row
        :name="property"
        :css-class="cssClass"
        :error-prefix="errorPrefix">
        <el-checkbox
            v-model="model"
            :disabled="disabled"
            :readonly="readonly"
            @change="changed">
            {{ label }}
        </el-checkbox>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
};
</script>