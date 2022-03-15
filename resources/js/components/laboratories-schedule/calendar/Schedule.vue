<template>
    <page
        :title="__('Календарь выходных лабораторий')">
        <section class="grey-cap grey-cap-220">
            <el-checkbox-group v-model="selectedList">
                <calendar
                    ref="calendar"
                    class="theme-default holiday-us-traditional holiday-us-official day-sheet-schedule"
                    :data-manager="dataManager"
                    initial-period="month"
                    :show-period-select="false"
                    :show-today="false"
                    weekday-name-format="long"
                    :starting-day-of-week="1"
                    row-height="130px"
                    event-top="2.8em" >
                    <template slot="header-actions">
                        <div class="buttons">
                            <!--<span class="inline-block mr-20">
                                <el-dropdown @command="handleCommand" class="action-link">
                                    <span class="el-dropdown-link">
                                        {{ activeClinicName }}<i class="el-icon-caret-bottom el-icon&#45;&#45;right"></i>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <template v-for="item in clinicsList">
                                            <el-dropdown-item
                                                :key="item.clinic_id"
                                                :command="item.clinic_id">
                                                {{ item.clinic_name }}
                                            </el-dropdown-item>
                                        </template>
                                    </el-dropdown-menu>
                                </el-dropdown>
                            </span>-->
                            <a href="#"
                               class="mr-20"
                               @click.prevent="deleteDayOff">
                                <svg-icon name="delete" class="action-link icon-small icon-blue" >
                                    {{ __('Удалить выходной') }}
                                </svg-icon>
                            </a>
                            <a href="#"
                                class="mr-20"
                                @click.prevent="addDayOff">
                                <svg-icon name="time-alt" class="action-link icon-small icon-blue" >
                                    {{ __('Задать выходной') }}
                                </svg-icon>
                            </a>
                            <a href="#"
                                @click.prevent="clearSelected">
                                <svg-icon name="dismiss-alt" class="action-link icon-small icon-blue" >
                                    {{ __('Сбросить выделения') }}
                                </svg-icon>
                            </a>
                        </div>
                    </template>
                    <template
                        slot="dayContent"
                        slot-scope="day">
                        <el-checkbox
                            :label="yearMonthDay(day.day)"
                            :style="{position: 'absolute', right:0}">&nbsp;
                        </el-checkbox>
                    </template>
                    <template
                        slot="event"
                        slot-scope="item">
                        <day-item :item="item" />
                    </template>
                </calendar>
            </el-checkbox-group>
        </section>
    </page>
</template>

<script>
import LaboratoriesScheduleRepository from '@/repositories/laboratories-schedule';
import LaboratoriesSchedule from '@/models/lab-schedule';
import DayItem from './DayItem.vue';

export default {
    components: {
        DayItem,
    },
    data() {

        return {
            filters: null,
            repository: new LaboratoriesScheduleRepository(),
            title: '',
            selectedList: [],
            range: {},
        }
    },
    methods: {
        handleCommand(command) {
            this.activeClinic = command;
        },
        clearSelected() {
            this.selectedList = [];
        },
        dataManager(from, to) {
            let filters = this.makeFilters(from, to);
            return this.repository.fetchList(filters).then((response) => {
                return response.data;
            });
        },
        makeFilters(from, to) {
            let filters = {...this.filters};

            if(from == undefined && to == undefined) {
                filters.dateStart = this.range.from;
                filters.dateEnd = this.range.to;
            } else {
                this.range = {from: from, to: to};
                filters.dateStart = from;
                filters.dateEnd = to;
            }

            return filters;
        },
        yearMonthDay(day) {
            return this.$formatter.dateFormat(day, "YYYY-MM-DD");
        },
        deleteDayOff(){
            if(!this.checkSelected()){
                return;
            }

            let model = new LaboratoriesSchedule();

            model.deleteDates(this.selectedList).then((response) => {
                this.$info(__('Выходные были удалены'));
                this.$emit('deleted', model);
                this.update();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        addDayOff(){
            if(!this.checkSelected()){
                return;
            }

            let model = new LaboratoriesSchedule();
            model.dates = this.selectedList;

            model.save().then((response) => {
                this.$info(__('Выходной задан'));
                this.$emit('created', model);
                this.update();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        checkSelected(){
            if (this.selectedList.length === 0){
                this.$error(__('Выберите хотя бы один день'));
                return false;
            }

            return true
        },
        getCalendar() {
            return this.$refs.calendar;
        },
        update(){
            let calendar = this.getCalendar();
            calendar.events = [];
            calendar.additionalFilterChangedCallback();
            this.clearSelected();
        },
    }
}
</script>
