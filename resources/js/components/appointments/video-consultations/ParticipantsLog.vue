<template>
    <section>
        <manage-table
            :fields="fields"
            :repository="repository"
            :enable-loader="false"
        >
        </manage-table>
        <div class="dialog-footer text-right">
            <el-button @click="cancel">
                {{ __('Закрыть') }}
            </el-button>
        </div>
    </section>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import {datetimeFormat} from "@/services/format";

export default {
    props: {
        participants: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.participants,
                });
            }),
            fields: [
                {
                    name: 'identity',
                    title: __('Имя'),
                    width: '4%',
                },
                {
                    name: 'duration',
                    title: __('Длительность (сек.)'),
                    width: '5%',
                },
                {
                    name: 'startTime.date',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    },
                    title: __('Старт'),
                    width: '5%',
                },
                {
                    name: 'endTime.date',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    },
                    title: __('Конец'),
                    width: '5%',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '5%',
                },
            ]
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
    },
}
</script>
