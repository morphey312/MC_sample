<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :scopes="scopes"
        table-height="400px"
        @header-filter-updated="syncFilters">
        <template
            slot="event"
            slot-scope="props" >
            <a href="" @click.prevent="showEmail(props.rowData.event_data.html)" v-if="props.rowData.event_data.html" v-text="$handbook.getOption('email_status', props.rowData.event)"></a>
            <span v-else> {{ $handbook.getOption('email_status', props.rowData.event) }} </span>
        </template>
        <template
            slot="attachments"
            slot-scope="props" >
            <div
                style="margin-top: 5px;"
                v-for="attachment in props.rowData.attachments_data"
                :key="attachment.id">
                <a @click.prevent="view(attachment.url, __('Просмотр отправленного файла',), props.rowData)">{{ attachment.name }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import EmailLogRepository from "@/repositories/email-log";
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import FileActionMixin from '@/mixins/file-action';
import Echo from "@/components/doctor/appointment/patient-history/modals/Echo";
import FileViewerHeader from "@/components/general/FileViewerHeader";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        id: Number
    },
    data() {
        return {
            repository: new EmailLogRepository(),
            fields: [
                {
                    name: 'event',
                    sortField: 'event',
                    filterField: 'event',
                    title: __('Событие'),
                    width: '10%',
                    filter: 'email_status',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'from',
                    sortField: 'from',
                    filterField: 'from',
                    title: __('От'),
                    width: '10%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'to',
                    sortField: 'to',
                    filter: true,
                    filterField: 'to',
                    title: __('Получатель'),
                    width: '10%',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'subject',
                    sortField: 'subject',
                    filter: true,
                    filterField: 'subject',
                    title: __('Тема'),
                    width: '10%',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'attachments',
                    filterField: 'attachments',
                    filter: 'yes_no',
                    title: __('Вложения'),
                    width: '10%',
                },
                {
                    name: 'created',
                    sortField: 'created',
                    filterField: 'created',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    },
                    title: __('Дата'),
                    width: '10%',
                    filter: DateHeaderFilter,
                },

            ],
            filters: {analysis_id: this.id},
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
                {field: 'id', direction: 'desc'},
            ],
            scopes: [
                'attachments',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        showEmail(html){
            let header = __('Просмотр отправленного e-mail');

            this.$modalComponent(Echo, {
                record: {html: html}
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header,
                width: '835px',
                customClass: 'no-footer',
                headerAddon: {
                    component: FileViewerHeader,
                    props:{
                        showDownload: false
                    },
                    eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        downloadFile(dialog){
                            dialog.getTopComponent().download();
                        }
                    },
                }

            })
        }
    },
}
</script>
