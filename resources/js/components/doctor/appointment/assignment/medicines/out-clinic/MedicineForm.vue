<template>
    <div class="time-sheet-wrapper">
        <form>
            <div></div>
            <el-row
                v-for="(row, index) in rows"
                :key="index"
                class="time-sheet-item p-20"
            >
                <el-col :span="24">
                    <medicine-select
                        :entity="row"
                        property="name"
                        :filterable="true"
                        :allow-create="true"
                        :featured-list="featuredList"
                        :clinic-works-with-apteka24="clinicWorksWithApteka24"
                        :label="__('Название')"
                        :featured-outclinic-list="featuredOutclinicList"
                    >
                        <span
                            v-if="index > 0"
                            slot="label-addon"
                            class="color-danger cursor-pointer"
                            @click="removeRow(index)"
                        >
                            {{ __("Удалить") }}
                        </span>
                    </medicine-select>
                    <form-row name="medication-days">
                        <form-input-number
                            :entity="row"
                            property="medication_duration"
                            control-size="mini"
                            css-class="table-row"
                            :label="__('Длительность приема, дней')"
                        />
                    </form-row>
                    <form-row name="quantity">
                        <form-input-number
                            :entity="row"
                            property="quantity"
                            control-size="mini"
                            css-class="table-row"
                            :label="__('Количество, шт')"
                        />
                    </form-row>
                    <form-text
                        :entity="row"
                        property="comment"
                        :rows="1"
                        :label="__('Комментарий')"
                    />
                </el-col>
            </el-row>
        </form>
        <el-row class="p-20 bb-grey-sh3 text-right">
            <el-button @click="addRow">
                {{ __("Добавить еще") }}
            </el-button>
        </el-row>
        <div class="p-10 text-right">
            <el-button @click="cancel">
                {{ __("Отменить") }}
            </el-button>
            <el-button
                type="primary"
                @click="selected"
            >
                {{ __("Добавить") }}
            </el-button>
        </div>
    </div>
</template>
<script>
import MedicineSelect from '@/components/general/form/Apteka24Medicine.vue'
import OutclinicMedicine from "@/models/employee/outclinic-medicine";
import AssignedMedicine from "@/models/patient/assigned-medicine";

export default {
    components: {
        MedicineSelect
    },
    props: {
        featuredOutclinicList: {
            type: Array,
            default: () => [],
        },
        clinicWorksWithApteka24: Boolean,
        clinic: Number
    },
    data() {
        return {
            rows: [],
            featuredList: this.getFeaturedList(),
        }
    },
    mounted() {
        this.addRow();
    },
    methods: {
        getFeaturedList() {
            return this.featuredOutclinicList.map(item => {
                item.value = item.name;
                return item;
            });
        },
        cancel() {
            this.$emit("cancel");
        },
        selected() {
            this.$emit("selected", this.prepareRows());
        },
        prepareRows() {
            let featuredList = [...this.featuredOutclinicList];
            return this.rows.map(row => {
                let featured = featuredList.find(item => item.id == row.name);
                if (featured != undefined) {
                    row.id = featured.id;
                    row.name = featured.name;
                    row.is_apteka24 = featured.is_apteka24;
                    row.apteka24_id = featured.apteka24_id;
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
            let row = new OutclinicMedicine();
            this.rows.push(row);
        }
    }
};
</script>
