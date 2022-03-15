<template>
    <manage-table 
        :fields="fields"
        :repository="repository"
        :enable-loader="false">
        <template 
            slot="contact" 
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    {{ __('Неизвестный') }}
                </span>
                <context-menu>
                    <a
                        v-if="$canProcessCalls()"
                        href="#"
                        @click.prevent="selectContactNumber(props.rowData.phone_number)">
                        {{ __('Задать контакт для звонка') }}
                    </a>
                </context-menu>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        processLog: Object,
    },
    data() {
        return {
            fields: [
                {
                    name: 'contact',
                    title: __('Контакт'),
                    width: '40%',
                },
                {
                    name: 'phone_number',
                    title: __('Номер телефона'),
                    width: '40%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                },
                {
                    name: 'processed_at',
                    title: __('Обработка завершена'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: [this.processLog].map((log) => ({
                    phone_number: log.phone_number,
                    processed_at: log.processed_at,
                }))
            })),
        };
    },
    methods: {
        selectContactNumber(number) {
            this.$emit('select-unknown', number);
        },
    },
};
</script>
