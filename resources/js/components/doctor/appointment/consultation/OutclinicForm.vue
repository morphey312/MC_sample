<template>
    <div class="time-sheet-wrapper">
        <form>
            <div></div>
            <el-row
                v-for="(row, index) in rows"
                :key="index"
                class="time-sheet-item p-20" >
                <el-col :span="24">
                    <form-select
                        :entity="row"
                        property="name"
                        :filterable="true"
                        :options="featuredList"
                        :allow-create="true"
                        :label="__('Специализация')">
                        <span
                            v-if="index > 0"
                            slot="label-addon"
                            class="color-danger cursor-pointer"
                            @click="removeRow(index)">
                            {{ __('Удалить') }}
                        </span>
                    </form-select>
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
                @click="selected">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import OutclinicSpecialization from "@/models/employee/outclinic-specialization";

export default {
    props: {
        consultations: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            rows: [],
            featuredList: this.getOutclinicList(),
        }
    },
    mounted() {
        this.addRow();
    },
    methods: {
        getOutclinicList() {
            return this.consultations.map(item => {
                item.value = item.name;
                return item;
            });
        },
        cancel() {
            this.$emit('cancel')
        },
        selected() {
            this.$emit('selected', this.prepareRows());
        },
        prepareRows() {
            let featuredList = [...this.consultations];
            return this.rows.map(row => {
                let featured = featuredList.find(item => item.id == row.name);
                if (featured !== undefined) {
                    row.id = featured.id;
                    row.name = featured.name;
                } else {
                    row.id = null;
                }
                return row;
            });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        addRow() {
            this.rows.push(new OutclinicSpecialization());
        },
    },
}
</script>
