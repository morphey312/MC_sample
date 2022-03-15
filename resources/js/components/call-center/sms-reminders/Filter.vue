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
                    property="patient_number"
                    :label="__('Номер телефона')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="delivery_status"
                    :clearable="true"
                    property="status"
                    :multiple="true"
                    :label="__('Статус')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :clearable="true"
                    :options="templates"
                    property="template"
                    :multiple="true"
                    :label="__('Шаблон')"
                />
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
import NotificationTemplateRepository from "@/repositories/notification/template";

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
            templates: new NotificationTemplateRepository()
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                date_from: null,
                date_to: null,

                ...fromState,
            };
        },
        refresh() {
            this.changed(this.filter);
        },
    },
};
</script>
