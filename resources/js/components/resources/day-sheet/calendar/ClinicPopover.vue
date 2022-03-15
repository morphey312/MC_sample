<template>
    <el-popover
        placement="right"
        width="400"
        trigger="manual"
        v-model="visible"
        popper-class="popover-right">
        <table class="vuetable table bb-0">
            <tr>
                <td>{{ clinic.name }}</td>
            </tr>
            <template v-for="(day, date) in clinic.sheets">
                <tr><td>{{ formatDate(date) }}</td></tr>
                <tr>
                    <td>
                        <table>
                            <tr v-for="period in day">
                                <td class="no-ellipsis">{{ period.time_from }} - {{ period.time_to }}</td>
                                <td class="no-ellipsis">{{ period.specialization_names.join(', ') }}</td>
                            </tr>
                        </table>    
                    </td>
                </tr>
            </template>
        </table>
        </div>
        <span 
            slot="reference"
            class="popover-toggle"
            :class="{'active': visible}"
            @click="visible = !visible">
            {{ clinic.name }}
        </span>
    </el-popover>
</template>
<script>
export default {
    props: {
        clinic: Object
    },
    data() {
        return {
            visible: this.clinic.visible
        }
    },
    methods: {
        formatDate(date) {
            return this.$formatter.dateFormat(date);
        }
    },
}   
</script>
