<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        @header-filter-updated="syncFilters">
        <template
            slot="remove"
            slot-scope="props" >
            <span @click="remove(props.rowData)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
        <template
            slot="name"
            slot-scope="props">
            <a
                href="#"
                @click.prevent="view(props.rowData.file_data.url)">
                {{ props.rowData.description || props.rowData.file_data.name}}
            </a>
        </template>
        <template slot="footer-top">
            <div
                slot="buttons"
                class="buttons">
                <el-button
                    v-if="$canCreate('patient-uploads')"
                    @click="addDocument">
                    {{ __('Добавить документ') }}
                </el-button>
                <el-button
                    @click="showLog">
                    {{ __('Операции') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>

<script>
import UploadedDocumentRepository from '@/repositories/patient/uploaded-document';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS  from '@/constants';
import FileActionMixin from '@/mixins/file-action';
import CreateDocumentForm from './uploaded/Create.vue';
import UploadLog from "@/components/action-log/patient/Upload.vue";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new UploadedDocumentRepository(),
            filters: {
                patient: this.patient.id,
            },
            fields: [
                {
                    name: 'name',
                    filterField: 'name',
                    title: __('Описание документа'),
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата'),
                    width: '10%',
                    filter: DateHeaderFilter,
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'type',
                    sortField: 'type',
                    title: __('Тип'),
                    width: '15%',
                    filter: 'patient_upload_type',
                    formatter: (value) => {
                        return this.$handbook.getOption('patient_upload_type', value);
                    },
                },
                ...(this.$can('patient-uploads.delete') ? [   {
                    name: 'remove',
                    title: '',
                    width: '35px',
                    dataClass: 'no-ellipsis no-dash',
                },] : []),
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({
                ...this.filters,
                ...updates,
            });
        },
        getTable() {
            return this.$refs.table;
        },
        showLog() {
            this.$modalComponent(UploadLog, {
                id: this.patient.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        addDocument() {
            this.$modalComponent(CreateDocumentForm, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, record) => {
                    dialog.close();
                    this.getTable().refresh();
                },
            },
            {
                header: __('Загрузить документ пациента: {name}', {name: this.patient.full_name}),
                width: '450px',
            });
        },
        remove(document) {
            this.$confirm(__('Вы уверены, что хотите удалить документ?'), () => {
                document.delete().then(() => {
                    this.getTable().refresh();
                    this.$info(__('Документ удалён успешно!'));
                }).catch(() => {
                    this.$error(__('Не удалось удалить документ'));
                });
            });
        }
    },
};
</script>
