<template>
    <div class="has-icon">
        <div class="ellipsis">
            {{ model.doctor.full_name }}
        </div>
        <el-popover
            v-if="$can('limitations.access-clinic') && isEmployee"
            placement="bottom"
            min-width="340px"
            trigger="click">
            <p v-if="outOfLimit"><b class="color-danger">{{ __('У врача превышен лимит по записям первичных пациентов') }}</b></p>
            <table class="vuetable ui blue unstackable celled table fixed text-left analysis-table-wrapper">
                <thead>
                    <tr>
                        <td class="no-ellipsis"><b>{{ title }}</b></td>
                        <template v-for="(title, index) in tableData.specializations">
                            <td class="no-ellipsis" :key="index">
                                <b>{{ title }}</b>
                            </td>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, trIndex) in tableData.rows" :key="trIndex">
                        <td
                            class="no-ellipsis"
                            :class="{'bg-danger': titleDanger(row)}">
                            {{ row.title }}
                        </td>
                        <template v-for="(val, tdIndex) in row.values">
                            <template v-if="row.limitClass" >
                                <td :key="tdIndex" class="no-ellipsis" :class="row.limitClass[val]">{{ val }}</td>
                            </template>
                            <template v-else >
                                <td :key="tdIndex" class="no-ellipsis">{{ val }}</td>
                            </template>
                        </template>
                    </tr>
                </tbody>
            </table>
            <template slot="reference">
                <svg-icon name="info-alt" class="icon-tiny" :class="iconClass" />
            </template>
        </el-popover>
    </div>
</template>
<script>
import CONSTANTS from '@/constants';

export default {
    props: {
        model: {
            type: Object
        },
    },
    computed: {
        title() {
            return __('Табель:') + ' ' + this.$formatter.dateFormat(this.model.date, 'DD MMM. YYYY');
        },
        tableData() {
            return this.createTableData();
        },
        outOfLimit() {
            return this.model._isOutOfLimit === true;
        },
        isEmployee() {
            return this.model.day_sheet_owner_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE;
        },
        iconClass() {
            return this.outOfLimit ? 'icon-red' : 'icon-grey';
        },
    },
    methods: {
        titleDanger(row) {
            return row.limitClass && this.outOfLimit;
        },
        createTableData() {
            let data = {
                specializations: [],
                rows: {
                    doctorFirst: {
                        title: __('Количество первичных записей:'),
                        values: [],
                    },
                    totalFirst: {
                        title: __('Количество первичных в отделении:'),
                        values: [],
                    },
                    percent: {
                        title: __('Процент записи первичных, %:'),
                        values: [],
                        limitClass: {}
                    },
                    totalPercent: {
                        title: __('Максимальный процент записей, %:'),
                        values: [],
                    },
                }
            };

            this.model.limitation_data.forEach((item) => {
                data.specializations.push(this.getName(item.specialization_id));
                data.rows.doctorFirst.values.push(item.doctor_is_first_total);
                data.rows.totalFirst.values.push(item.specialization_is_first);
                let currentPercent = this.model.getCurrentLimitationPercent(item)
                data.rows.percent.values.push(currentPercent);
                data.rows.totalPercent.values.push(item.limitation_percent);

                if(currentPercent > item.limitation_percent) {
                    data.rows.percent.limitClass[currentPercent] = 'bg-danger';
                } else {
                    data.rows.percent.limitClass[currentPercent] = '';
                }
            });
            return data;
        },
        getName(id) {
            return _.find(this.model.doctor.specializations, {id: id}).name;
        },
    }
}
</script>
