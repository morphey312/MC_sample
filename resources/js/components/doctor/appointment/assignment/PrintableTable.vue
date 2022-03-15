<template>
    <div v-if="list.length != 0">
        <h3>{{ title }}</h3>
        <div class="printable-table">
            <table class="table">
                <thead v-if="header">
                    <tr>
                        <th v-for="(field, index) in fields" :key="index">
                            {{ field.title }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in list" :key="index">
                        <td 
                            v-for="(field, num) in fields" 
                            :key="`td${num}`"
                            :class="`${field.dataClass || ''}`">
                            {{ getValue(item, field) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        list: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: '',
        },
        fields: {
            type: Array,
            default: () => [],
        },
        header: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        getValue(item, field) {
            let val =  _.get(item, field.name);
            if (field.formatter != undefined) {
                return field.formatter(val);
            }
            return val;
        },
    }
}
</script>