<template>
    <div class="time-sheet-wrapper">
        <form>
            <div></div>
            <el-row
                v-for="(row, index) in rows"
                :key="index"
                class="time-sheet-item p-20" >
                <el-col :span="24">
                    <form-input
                        :entity="row"
                        property="name"
                        :error-prefix="`e${index}.`"
                        :label="__('Название')">
                        <span
                            v-if="index > 0"
                            slot="label-addon"
                            class="color-danger cursor-pointer"
                            @click="removeRow(index)">
                            {{ __('Удалить') }}
                        </span>
                    </form-input>
                    <form-text
                        :entity="row"
                        property="comment"
                        :rows="6"
                        :label="__('Дополнительное описание')"
                    />
                </el-col>
            </el-row>
        </form>
        <el-row class="p-20 bb-grey-sh3 text-right">
            <el-button @click="addRow">
                {{ __('Добавить еще') }}
            </el-button>
        </el-row>
        <div class="p-10 text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="hasMissingName"
                @click="selected">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import OutclinicDiagnostic from "@/models/employee/outclinic-diagnostic";

export default {
    data() {
        return {
            rows: [],
        }
    },
    computed: {
        hasMissingName() {
            return this.rows.some((row) => _.isVoid(row.name));
        },
    },
    mounted() {
        this.addRow();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        selected() {
            this.$emit('selected', this.rows);
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        addRow() {
            this.rows.push(new OutclinicDiagnostic());
        },
    },
}
</script>
