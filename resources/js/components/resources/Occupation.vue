<template>
     <page
        :title="__('Стационарные палаты')"
        type="flex"
        v-loading="loading">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <timeline-filter
                    ref="patientFilter" 
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section>
            <template v-if="showTimeline">
                <div class="wrapper">
                    <form-button 
                        :text="__('hour')"
                        @click="hour" />
                    <form-button 
                        :text="__('month')"
                        @click="month" />
                    <form-button 
                        :text="__('24')"
                        @click="edit" />
                    <form-button
                        :text="__('zoomIn')"
                        @click="zoomIn" />
                    <form-button
                        :text="__('zoomOut')"
                        @click="zoomOut" />
                    <div id="timeline-visualization"></div>
                </div>
            </template>
            <template v-else>
                <div>
                    {{ __('Выберите период и клинику для отображения') }}
                </div>
            </template>
        </section>
     </page>
</template>
<script>
import Vue from 'vue';
import { Timeline } from 'vis-timeline/standalone';
import ManageMixin from '@/mixins/manage';
import TimelineFilter from './occupation/Filter.vue';
import FormEdit from './occupation/FormEdit.vue';
import Item from './occupation/Item.vue';
import OccupationRepository from '@/repositories/department/room/occupation';
import RoomRepository from '@/repositories/department/room';
import Occupation from '@/models/department/room/occupation';
import SearchPatient from '@/components/patients/search/Search.vue';
import TogglePatientFilter from '@/components/patients/search/ToggleFilter.vue';
import CONSTANTS from '@/constants';

const ItemComponent = Vue.extend(Item);

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        TimelineFilter,
    },
    data() {
        let today = this.$moment().format('YYYY-MM-DD');

        return {
            displayFilter: true,
            roomRepository: new RoomRepository(),
            repository: new OccupationRepository(),
            timeline: null,
            loading: false,
            showTimeline: false,
            items: [],
            groups: [],
            options: {
                template: (item) => {
                    return new ItemComponent({
                        propsData: {
                            item: item.model,
                        },
                    }).$mount().$el;
                },
                locale: 'uk',
                orientation: "top",
                itemsAlwaysDraggable:true,
                stack: false,
                timeAxis: {
                    scale: "hour",
                    step: 1,
                },
                minHeight:"200px",
                editable: {
                    add: true, // add new items by double tapping
                    updateTime: true, // drag items horizontally
                    updateGroup: false, // drag items from one group to another
                    remove: true, // delete an item by tapping the delete button top right
                    overrideItems: false // allow these options to override item.editable
                },
                onUpdate: (item, callback) => this.onUpdate(item, callback), //Fires event on update
                onMove: (item, callback) => this.onMove(item, callback),
                onRemove: (item, callback) => this.onRemove(item, callback),
                onAdd: (item, callback) => this.onAdd(item, callback),
            },
        }
    },
    methods: {
        changeFilters(filters) {
            this.storeFilter(filters);
            this.filters = filters;
            this.getData();
        },
        clearFilters() {
            this.forgetFilter();
            this.filters = this.getDefaultFilters();
            this.clearTimeLine();
        },
        clearTimeLine() {
            this.showTimeline = false;
            this.timeline = null;
        },
        init() {
            let container = document.getElementById('timeline-visualization');
            this.timeline = new Timeline(container, this.items, this.groups, this.options);
            this.hour();
            // this.timeline.on('rangechanged', (start, end) => {
            //     // this.loading = true;
            //     // console.log('rangechanged', start, end);
            //     // this.getData().then(() => {
            //     //     this.loading = false;
            //     // });
            // });
            // this.timeline.on('contextmenu', (props) => {
            //     console.log(props); //pros.item null||item.id
            //     props.event.preventDefault();
            // });
        },
        onAdd(item, callback) {
            let group = this.groups.find(group => group.id == item.group);
            
            if (!group) {
                this.$error(__('Не удалось найти место'));
                return callback(null);
            }

            if (group.treeLevel != CONSTANTS.PLACE_OCCUPATION.TIMELINE.LEVEL.PLACE) {
                this.$error(__('Выберите свободное время койки'));
                return callback(null);
            }

            this.findPatient((patient) => {
                let model = new Occupation({
                    room_id: group.roomId,
                    place_id: group.placeId,
                    patient_id: patient.id,
                    ...this.getModelRanges(item),
                });
                model.save().then(() => {
                    this.updateTimeline(model, callback);
                    this.$info(__('Запись успешно создана'));
                }).catch((e) => {
                    console.log(e);
                });
            });
        },
        onRemove(item, callback) {
            
        },
        onMove(item, callback) {
            let itemStart = this.$moment(item.start);
            let itemEnd = this.$moment(item.end);
            let lineItems = this.getItems(); 

            let cross = Object.keys(lineItems).find(key => {
                let data = lineItems[key].data;
                let rowStart = this.$moment(data.start);
                let rowEnd = this.$moment(data.end);

                return data.id != item.id 
                    && data.group == item.group
                    && (itemStart.isBetween(rowStart, rowEnd, null, '[)') || itemEnd.isBetween(rowStart, rowEnd, null, '(]'));
            });
            
            if (cross) {
                this.$error(__('Пересекается с другой записью'));
                return callback(null);
            }

            let start = this.$moment(item.start);
            let end = this.$moment(item.end);
            let model = item.model;
            model.date_from = this.formatDate(start.clone());
            model.date_to = this.formatDate(end.clone());
            model.start = this.formatTime(start.clone());
            model.end = this.formatTime(end.clone());
            model.save().then(() => {
                this.updateTimeline(model, callback);
            }).catch((e) => {
                callback(null);
                this.$displayErrors(e);
            });;
        },
        onUpdate(item, callback) {
            return this.openForm(item, callback);
        },
        getData() {
            this.showTimeline = false;
            this.loading = true;
            this.updateOptions();
            return this.roomRepository.fetchSchedule(_.onlyFilled({...this.filters}))
                .then((response) => {
                    this.prepareData(response);
                    this.showTimeline = true;
                    return this.$nextTick(() => {
                        this.init();
                        this.loading = false;
                        return Promise.resolve();
                    });
                });
        },
        prepareGroups(rows) {
            let groups = [];
            let departments = _.groupBy(rows, 'department_id');
            Object.keys(departments).forEach(departmentId => {
                let rooms = departments[departmentId];
                let name = rooms[0].department_name;

                groups.push({
                    id: name + departmentId,
                    departmentId: Number(departmentId),
                    title: name,
                    content: name,
                    treeLevel: CONSTANTS.PLACE_OCCUPATION.TIMELINE.LEVEL.DEPARTMENT,
                    className: 'events-none',
                    nestedGroups: rooms.map(room => room.name + room.id),
                });

                rooms.forEach(room => {
                    groups.push({
                        id: room.name + room.id,
                        roomId: room.id,
                        title: room.name,
                        content: room.name,
                        treeLevel: CONSTANTS.PLACE_OCCUPATION.TIMELINE.LEVEL.ROOM,
                        className: 'events-none',
                        nestedGroups: room.places.map(place => place.number + '-' + place.id),
                    });

                    room.places.forEach(place => {
                        groups.push({
                            id: place.number + '-' + place.id,
                            placeId: place.id,
                            roomId: room.id,
                            content: __('Койка: {number}', {number: place.number}),
                            treeLevel: CONSTANTS.PLACE_OCCUPATION.TIMELINE.LEVEL.PLACE,
                        }); 
                    });
                });
            });
            this.groups = groups;
        },
        prepareItems(rows) {
            let items = [];

            rows.forEach(room => {
                let places = room.places;
                room.occupations.forEach(item => {
                    items.push({
                        id: item.id,
                        group: this.getOccupationGroup(item, places),
                        start: this.$moment(item.date_from + ' ' + item.start),
                        end: this.$moment(item.date_to + ' ' + item.end),
                        type: CONSTANTS.PLACE_OCCUPATION.TIMELINE.ITEM_TYPE,
                        model: item,
                    });
                });
            });

            this.items = items;
        },
        getOccupationGroup(item, places) {
            let place = places.find(place => place.id === item.place_id);
            return place ? (place.number + '-' + place.id) : null;
        },
        findPatient(onSelected = null) {
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    if (onSelected) {
                        onSelected(patient);
                    }
                },
            }, {
                header: __('Фильтр поиска контактных лиц'),
                width: '1270px',
                headerAddon: {
                    component: TogglePatientFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        openForm(item, callback) {
            this.$modalComponent(FormEdit, {
                item: item,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, model) => {
                    dialog.close();
                    this.updateTimeline(model, callback);
                },
            }, {
                header: __('Запись в стационар'),
                width: '900px',
            });
        },
        updateTimeline(model, callback) {
            let item = {
                id: model.id,
                group: model.place.number + '-' + model.place_id,
                start: this.$moment(model.date_from + ' ' + model.start),
                end: this.$moment(model.date_to + ' ' + model.end),
                type: CONSTANTS.PLACE_OCCUPATION.TIMELINE.ITEM_TYPE,
                model: model,
            };
            let itemIndex = this.items.findIndex(i => i.id == item.id);
            if (itemIndex === -1) {
                this.items.push(item);
            } else {
                this.items.splice(itemIndex, 1, item);
            }
            this.$info(__('Запись успешно изменена'));
            callback(item);
        },
        updateOptions() {
            this.options.min = this.filters.date_from;
            this.options.start = this.filters.date_from;
            this.options.max = this.formatDate(this.$moment(this.filters.date_to).add(1, 'days'));
            this.options.end = this.filters.date_to;
        },
        getModelRanges(item) {
            let selectedTime = this.$moment(item.start);
            let date_from = this.formatDate(selectedTime.clone());
            let date_to = this.formatDate(selectedTime.clone());
            let start = this.formatTime(selectedTime.clone());
            let end = this.formatTime(selectedTime.clone().add(1, 'hours'));
            return {date_from, date_to, start, end};
        },
        formatDate(date, format = 'YYYY-MM-DD') {
            return this.$formatter.dateFormat(date, format);
        },
        formatTime(date) {
            return this.formatDate(date, 'HH:mm:ss');
        },
        prepareData(rows = []) {
            this.prepareGroups(rows);
            this.prepareItems(rows);
        },
        getItems() {
            return this.timeline.itemSet.items;
        },
        edit() {
            this.timeline.setWindow('2020-12-20', '2020-12-25');
        },
        month() {
            this.options.timeAxis.scale = 'month';
            this.timeline.setWindow('2020-12-01', '2020-12-31');
        },
        hour() {
            this.options.timeAxis.scale = 'hour';
            this.timeline.setWindow(this.filters.date_from, this.filters.date_to);
        },
        zoomIn() {
            this.timeline.zoomIn(1, {animation: true});
        },
        zoomOut() {
            this.timeline.zoomOut(1, {animation: true});
        },
    },
}
</script>