<template>
  <search-filter
    :model="filter"
    :show-submit-button="true"
    :show-clear-button="true"
    :auto-search="false"
    @changed="changed"
    @cleared="cleared"
  >
    <el-row :gutter="20">
      <el-col :span="3">
        <form-input-search
          :entity="filter"
          property="id"
          :clearable="true"
          :label="__('№ Вызова')"
        />
      </el-col>
      <el-col :span="3">
        <form-input-search
          :entity="filter"
          property="patient_card_number"
          :clearable="true"
          :label="__('№ Карты')"
        />
      </el-col>
      <el-col :span="6">
        <form-input-search
          :entity="filter"
          property="patient_name"
          :clearable="true"
          :label="__('Пациент')"
        />
      </el-col>
      <el-col :span="6">
        <form-select
          :entity="filter"
          options="district"
          property="type"
          :label="__('Район')"
        />
      </el-col>
      <el-col :span="6">
        <form-row name="dates" :label="__('Период')">
          <div class="form-input-group">
            <form-date :entity="filter" property="created_start" />
            <form-date :entity="filter" property="created_end" />
          </div>
        </form-row>
      </el-col>
    </el-row>
    <el-row :gutter="20">
              <el-col :span="6">
        <form-select
          :entity="filter"
          :repository="doctor"
          property="doctor"
          :clearable="true"
          :label="__('Врач')"
        />
      </el-col>
        </el-row>
  </search-filter>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';
import FilterMixin from "@/mixins/filter";
import CONSTANTS from '@/constants';

export default {
  mixins: [FilterMixin],
  data() {
    return {
      doctor: new EmployeeRepository({
        filters: { positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR },
      }),
    };
  },
  methods: {
    initFilter(fromState = {}) {
      this.filter = {
        phone: null,
        status: null,
        ...fromState,
      };
    },
  },
};
</script>
