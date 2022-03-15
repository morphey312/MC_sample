<template>
    <div>
        <div class="shedule-header-day">
            <a href="#" @click.prevent="toggleCollapse">{{ (collapsed ? __('Развернуть') : __('Свернуть лист записи')) }}</a>
        </div>
        <div class="shedule-header-day">
            <div class="schedule-switcher left" @click="thumbLeft">
                <i class="el-icon-caret-left"></i>
            </div>
            <div class="schedule-switcher right" @click="thumbRight">
                <i class="el-icon-caret-right"></i>
            </div>
            <span class="header-datepicker"
                  @click="focusDatepicker">
                {{ (collapsed ? info.week_day_short : info.week_day) }}
                <el-date-picker
                    v-model="dateForSelect"
                    ref="datepicker"
                    type="date"
                    value-format="yyyy-MM-dd"
                    :picker-options="pickerOptions"
                    @change="setDateForDaySheet">
                </el-date-picker>
            </span>
        </div>
        <div class="schedule-header-info">
            <div class="icon-delete-wrapper" @click="deleteCurrentList">
                <svg-icon name="delete" class="icon-small icon-blue"></svg-icon>
            </div>
            <div>
                <template v-if="collapsed">
                    <p>{{ info.time_schedule }} </p>
                    <p class="ellipsis">{{ info.specializations.names }}</p>
                    <p class="ellipsis">{{ info.doctor.full_name }} </p>
                </template>
                <template v-else>
                    <p>{{ info.time_schedule }} {{ vacation ? '' : '/' }} {{ info.specializations.names }}</p>
                    <p>{{ info.doctor.full_name }} {{ info.specializations.workspaces }}</p>
                    <p>{{ info.clinic_name }} {{ __('- Прием(мин):') }} {{ duration }} / {{ duration_repeated }}</p>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        day: {
            type: Object,
            required: true,
        },
        collapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            dateForSelect: null,
            pickerOptions: {
                firstDayOfWeek: 1,
            },
        }
    },
    computed: {
        info() {
            return this.makeHeaderData();
        },
        duration() {
            return this.info.doctor.appointment_duration ? this.info.doctor.appointment_duration : '--';
        },
        duration_repeated() {
            return this.info.doctor.appointment_duration_repeated ? this.info.doctor.appointment_duration_repeated : '--';
        },
        vacation() {
            return this.day.id === null;
        },
    },
    methods: {
        toggleCollapse() {
            this.$emit('toggle-collapse');
        },
        focusDatepicker() {
            this.dateForSelect = this.day.date;
            this.$refs.datepicker.focus();
        },
        setDateForDaySheet() {
            if(this.dateForSelect != this.day.date) {
                this.$emit('switch-day', { date: this.dateForSelect });
            }
        },
        thumbLeft() {
            this.$emit('thumb-left');
        },
        thumbRight() {
            this.$emit('thumb-right');
        },
        deleteCurrentList() {
            this.$eventHub.$emit('remove-daysheet-item', {
                params: {
                    date: this.day.date,
                    day_sheet_owner_id: this.day.doctor.id,
                    day_sheet_owner_type: this.day.day_sheet_owner_type,
                    clinic_id: this.day.clinic.id,
                    workspace_id: this.day.workspace_id,
                },
                daySheetId: this.day.id
            });
        },
        makeHeaderData() {
            let daySheet = this.day;

            let data = {
                clinic_name: daySheet.clinic.name,
                specializations: this.specializationsList(),
                doctor: daySheet.doctor,
            };

            data.week_day = this.$formatter.dateFormat(daySheet.date, 'dddd, DD MMM, YYYY');
            data.week_day_short = this.$formatter.dateFormat(daySheet.date, 'DD MMM, YYYY');

            let timeFirstSheet = _.first(daySheet.time_sheets);
            let timeLastSheet = _.last(daySheet.time_sheets);

            data.time_schedule = this.vacation
                ? ''
                : this.timeString(timeFirstSheet, 'time_from') + ' - ' + this.timeString(timeLastSheet, 'time_to');

            return data;
        },
        timeString(item, field, start = 0, length = 5) {
            return item ? item[field].substring(start, length) : '';
        },
        specializationsList() {
            let result = {
                names: [],
                workspaces: [],
            };

            _.each(this.day.time_sheets, (time_sheet) => {
                _.each(time_sheet.specialization_data, (item) => {
                    if(result.names.indexOf(item.name) === -1) {
                        result.names.push(item.name);
                    }

                    if (item.workspace.name && result.workspaces.indexOf(item.workspace.name) === -1) {
                        result.workspaces.push(item.workspace.name);
                    }
                });
            });

            result.names = result.names.join(', ');
            result.workspaces = (result.workspaces.length) != 0 ? `(${result.workspaces.join(', ')})` : '';

            return result;
        },
    },
}
</script>
