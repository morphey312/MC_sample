<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        table-height="auto">
        <template
            slot="template"
            slot-scope="props">
            <span v-text="props.rowData.template_name || props.rowData.name"></span>
        </template>
        <template
            slot="attachments_data"
            slot-scope="props" >
            <div v-if="props.rowData.type === CONSTANTS.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD">
                <div
                    v-for="outclinicProtocolRecord in props.rowData.attachments_data"
                    style="margin-top: 5px;">
                    <a
                        style="word-break: break-word;"
                        @click.prevent="view(outclinicProtocolRecord.url, __('Просмотр файла') + ' ' + outclinicProtocolRecord.name, props.rowData)"
                        v-text="outclinicProtocolRecord.name"></a>
                </div>
            </div>
            <div v-else>
                <a
                    href="#"
                    @click.prevent="view(props.rowData.file_data.url)">
                    {{ props.rowData.template_name || props.rowData.name }}
                </a>
            </div>
        </template>
        <template
            slot="delete"
            slot-scope="props" >
            <a class="" @click="remove(props.rowData)">
                <svg-icon name="delete" class="icon-small icon-blue"/>
            </a>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import FileActionMixin from '@/mixins/file-action';
import CONSTANTS  from '@/constants';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        researchResults: {
            type: Array,
            default: () => [],
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.researchResults,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название протокола'),
                },
                {
                    name: 'attachments_data',
                    title: __('Файлы'),
                    dataClass: 'no-ellipsis',
                    width: '40%',
                },
                ...(this.readonly === false && this.$can('doctor-cabinet.delete-research') ? [ {
                    name: 'delete',
                    title: '',
                    width: "5%",
                },] : []),

            ],
            CONSTANTS: CONSTANTS
        }
    },
    watch: {
        researchResults() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(research){
            research.delete().then(() => {
                this.$emit('research-deleted', research);
                this.$info(__('Исследование удалёно успешно !'));
            }).catch(() => {
                this.$error(__('Не удалось удалить исследование'));
            });
        },
        show(row) {
            if (row) {
                if (this.isValidType(row.file_data.type)) {
                    return this.view(row.file_data.url, row.file_data.name);
                }
                return this.errorFileFormat();
            }
        }
    },
}
</script>
