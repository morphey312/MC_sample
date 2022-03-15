<template>
    <section class="grey pb-0">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filters"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Дата')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filters"
                            property="date_start"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filters"
                            property="date_end"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
        </el-row>
    </section>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        filters: {},
        permission: String,
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited(this.permission),
            }),
        };
    },
    watch: {
        ['filters.date_start'](v) {
            this.filters.date_end = v;
        }
    },
};
</script>
