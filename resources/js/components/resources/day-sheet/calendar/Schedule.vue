<template>
    <page
        :title="title"
        :back-route="{name: 'day-sheets'}">
        <section v-if="activeClinic !== null" class="grey-cap grey-cap-220">
            <el-checkbox-group v-model="selectedList">
                <calendar
                    ref="calendar"
                    class="theme-default holiday-us-traditional holiday-us-official day-sheet-schedule"
                    :data-manager="dataManager"
                    initial-period="month"
                    :show-period-select="false"
                    :show-today="false"
                    :additional-filter="activeClinic"
                    weekday-name-format="long"
                    :starting-day-of-week="1"
                    row-height="130px"
                    @click-date="addDayToSelected"
                    @click-event="addDayToSelected"
                    event-top="2.8em" >
                    <template slot="header-actions">
                        <div class="buttons">
                            <span class="inline-block mr-20">
                                <el-dropdown @command="handleCommand" class="action-link">
                                    <span class="el-dropdown-link">
                                        {{ activeClinicName }}<i class="el-icon-caret-bottom el-icon--right"></i>
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
                            </span>
                            <a href="#" 
                                v-if="canUpdateDaySheet(activeClinic)"
                                class="mr-20"
                                @click.prevent="editTimeSheet">
                                <svg-icon name="time-alt" class="action-link icon-small icon-blue" >
                                    {{ __('Задать рабочее время') }}
                                </svg-icon>
                            </a>
                            <a href="#" 
                                v-if="canUpdateDaySheet(activeClinic)"
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
import Owner from '@/models/day-sheet/owner';
import DaySheetRepository from '@/repositories/day-sheet';
import ClinicRepository from '@/repositories/clinic';
import DayItem from './DayItem.vue';
import EmployeeForm from './EmployeeForm.vue';
import WorkspaceForm from './WorkspaceForm.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        DayItem,
    },
    data() {
        let routeFilters =  {};
        let ownerType = this.$route.params.owner_type;
        routeFilters[ownerType] = this.$route.params.id;

        return {
            filters: routeFilters,
            repository: new DaySheetRepository(),
            ownerType: ownerType,
            title: '',
            owner: null,
            selectedList: [],
            clinicsList: [],
            activeClinic: null,
            range: {},
            employeePosition: '',
            name: '',
            activeClinicName: '',
        }
    },
    beforeMount(){
        this.initData();
    },
    watch: {
        activeClinic() {
            this.getTitle();
            this.setActiveClinicName();
        },
    },
    methods: {
        handleCommand(command) {
            this.activeClinic = command;
        },
        clearSelected() {
            this.selectedList = [];
        },
        canUpdateDaySheet(clinic) {
            if (this.$can('day-sheets.update')) {
                return true;
            }
            if (this.$can('day-sheets.update-clinic')) {
                return this.$store.state.user.clinics.indexOf(clinic) !== -1;
            }
            return false;
        },
        setActiveClinicName() {
            let active = this.clinicsList.find(clinic => this.activeClinic === clinic.clinic_id);
            this.activeClinicName = active.clinic_name;
        },
        dataManager(from, to) {
            let filters = this.makeFilters(from, to);
            return this.repository.fetchUserSchedule(filters);
        },
        makeFilters(from, to) {
            let filters = {... this.filters, clinicSheet: this.activeClinic};

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
        getClinics() {
            if(this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
                this.clinicsList = this.getDoctorClinics();
            } else if (this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE) {
                this.clinicsList = this.owner.workspace_clinics;
            }
            
            if (this.$isAccessLimited('day-sheets')) {
                let allowedClinics = this.$store.state.user.clinics;
                this.clinicsList = this.clinicsList.filter((clinic) => allowedClinics.indexOf(clinic.clinic_id) !== -1);
            }

            this.activeClinic = this.clinicsList[0].clinic_id;
        },
        getDoctorClinics() {
            return this.owner.employee_clinics.filter((clinic) => {
                return _.isFilled(clinic.doctor.id) && (
                    clinic.status == CONSTANTS.EMPLOYEE.STATUSES.WORKING || 
                    clinic.status == CONSTANTS.EMPLOYEE.STATUSES.PROBATION
                );
            });
        },
        back() {
            this.$router.push({ name: 'day-sheets' });
        },
        initData() {
            this.getOwner().then(() =>  this.getClinics());
        },
        getOwner() {
            let owner = new Owner({id: this.filters[this.ownerType]});
            return owner.fetchOwner(this.ownerType, ['clinics','clinics.doctor','clinics.position','clinics.specializations','clinics.clinic']).then((model) => {
                this.owner = model;
                this.getTitle();
            });
        },
        getTitle() {
            let titleAddon = '';

            if(this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
                this.setEmployeePosition();
                this.name = this.owner.full_name;
                titleAddon = ' (' + this.employeePosition + ')';
            } else if (this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE) {
                this.name = this.owner.name;
            }

            this.title = __('Табель: {name} {suffix}', {name: this.name, suffix: titleAddon});
        },
        yearMonthDay(day) {
            return this.$formatter.dateFormat(day, "YYYY-MM-DD");
        },
        editTimeSheet() {
            if (this.selectedList.length == 0){
                this.$error(__('Выберите хотя бы один день'));
                return;
            }

            if (this.hasDifferentPeriods()) {
                this.$error(__('Выбраны дни с разным графиком. Изменения невозможны.'));
                return;
            }
            
            let formComponent;

            if (this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
                formComponent = EmployeeForm;
            } else if (this.ownerType === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE) {
                formComponent = WorkspaceForm;
            }

            this.$modalComponent(formComponent, {
                day_sheet_owner_id: this.$route.params.id,
                day_sheet_owner_type: this.ownerType,
                clinic_id: this.activeClinic,
                dateList: this.selectedList,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog) => {
                    dialog.close();
                    let calendar = this.getCalendar();
                    calendar.events = [];
                    calendar.additionalFilterChangedCallback();
                },
            }, {
                    header: __('Задать рабочее время'),
                    width: '400px',
            });
        },
        setEmployeePosition() {
            let clinic = _.find(this.owner.employee_clinics, (item) => {
                return item.clinic_id == this.activeClinic;
            });
            this.employeePosition = clinic ? clinic.position_name : '';
        },
        getCalendar() {
            return this.$refs.calendar;
        },
        hasDifferentPeriods() {
            let events = this.getDayItems();
            let isDifferent  = false;
            if (events.length == 1) {
                return isDifferent;
            }
            
            events.forEach((day, index) => {
                if (isDifferent == true) {
                    return;
                }

                //Verify days has same time sheets count
                let prevIndex = index - 1;
                if (prevIndex >= 0) {
                    if (events[prevIndex].time_sheets.length != day.time_sheets.length) {
                        isDifferent = true;
                    }
                }

                //If count is same, compare start of first sheets
                if (!isDifferent) {
                    let missMatch = events.find(event => {
                        if (event.id == day.id) {
                            return false;
                        }
                        return this.compareTimeSheets(event, day);
                    });

                    if (missMatch) {
                        isDifferent = true;
                    }
                }
            });
            return isDifferent;
        },
        getDayItems() {
            let calendar = this.getCalendar();
            return calendar.events.filter(event => {
                return this.selectedList.indexOf(event.startDate) !== -1;
            })
        },
        addDayToSelected(date) {
            if (date.startDate !== undefined) {
                date = date.startDate;
            }

            let formattedDate = this.$moment(date).format("YYYY-MM-DD");
            let index = this.selectedList.indexOf(formattedDate);

            if (index === -1) {
                this.selectedList.push(formattedDate);
            } else {
                this.selectedList.splice(index, 1);
            }
        },
        compareTimeSheets(event, day) {
            let missMatch = false;
            event.time_sheets.forEach((sheet, index) => {
                if (missMatch) {
                    return;
                }
                if (sheet.time_from != day.time_sheets[index].time_from || sheet.time_to != day.time_sheets[index].time_to) {
                    missMatch = true;
                }
            });
            return missMatch;
        },
    }
}
</script>
