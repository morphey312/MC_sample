<template>
    <div class="card-record-line">
        <field
            v-for="(field) in fields"
            :key="field.key"
            :field="field"
            :space="fieldWidth(field)" />
    </div>
</template>
<script>
import Field from './Field.vue';

export default {
    components: {
        Field,
    },
    props: {
        fields: Array,
    },
    data() {
        return {
            needDistribute: !this.fields.some((field) => {
                return field.width !== undefined || !field.hasInput();
            }),
        };
    },
    methods: {
        fieldWidth(field) {
            if (field.width !== undefined) {
                return field.width;
            }
            if (this.needDistribute) {
                return 100 / this.fields.length + '%'; 
            }
            return 'auto';
        },
    },
};
</script>