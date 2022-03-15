<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :show-collapse-button="true"
        :start-collapsed="startCollapsed"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период дат')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_from" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_to" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="phone_number"
                    :label="__('Номер телефона')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="voip_queue"
                    :multiple="true"
                    property="queue" 
                    :label="__('Очередь')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="delay"
                    property="missed" 
                    :label="__('Интервал')" />
            </el-col>
        </el-row>
        <template slot="extra-buttons">
            <el-button
                @click="refresh">
                {{ __('Обновить данные') }}
            </el-button>
        </template>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        startCollapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            delay: [
                {id: 1, value: __('1 час')},
                {id: 2, value: __('2 часа')},
                {id: 3, value: __('3 часа')},
                {id: 6, value: __('6 часов')},
                {id: 12, value: __('12 часов')},
                {id: 24, value: __('24 часа')},
                {id: 48, value: __('48 часов')},
            ],
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                date_from: null,
                date_to: null,
                phone_number: null,
                queue: [],
                missed: null,
                ...fromState,
            };
        },
        refresh() {
            this.changed(this.filter);
        },
    },
};
</script>