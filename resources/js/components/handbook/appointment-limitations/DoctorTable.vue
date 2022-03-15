<template>
    <div>
        <table v-if="doctors.length != 0" class="vuetable ui blue unstackable celled table fixed text-left">
            <thead>
                <tr>
                    <th>{{ __('Врачи') }}</th>
                    <th>{{ __('Макс. процент записи, %') }}</th>
                    <th>{{ __('Жесткое ограничение') }}</th>
                </tr>    
            </thead>
            <tbody>
                <tr v-for="(doctor, index) in doctors" :key="index">
                    <td width="55%">{{ doctor.full_name }}</td>
                    <td class="text-right">
                        <input
                            v-if="rowInput"
                            v-model="doctor.limitation_percent"
                            type="number"
                            :step="1"
                            :max="100"
                            :min="0"
                            :disabled="disable"
                            class="el-input__inner text-right"
                            @change="checkPercent" />
                        <template v-else>
                            {{ doctor.limitation_percent }}
                        </template>
                    </td>
                    <td>
                        <el-checkbox v-model="doctor.is_hard_limit" :disabled="disable" />
                    </td>
                </tr>    
            </tbody>
        </table>    
    </div>
</template>

<script>
export default {
    props: {
        doctors: Array,
        disable: {
            type: Boolean,
            default: false,
        },
        rowInput: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        checkPercent() {
            this.$emit('check-percent');
        }
    }
}   
</script>