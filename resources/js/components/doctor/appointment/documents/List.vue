<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        table-height="auto">
        <template
            slot="show"
            slot-scope="props" >
            <a class="" @click="show(props.rowData)">
                {{ __('Посмотреть') }}
            </a>
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

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        documents: {
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
                    rows: this.documents,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название документа'),
                },
                {
                    name: 'show',
                    title: '',
                    width: "129px",
                },
                ...(this.readonly === false && this.$can('doctor-cabinet.delete-documents') ? [{
                    name: 'delete',
                    title: '',
                    width: "40px",
                }] : []),
            ],
        }
    },
    watch: {
        documents() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(document){
            document.delete().then(() => {
                this.$emit('document-deleted', document);
                return this.$info(__('Документ удалён успешно !'));
            }).catch(() => {
                return this.$error(__('Не удалось удалить документ'));
            });
        },
        show(row) {
            if (row) {
                if (this.isValidType(row.attachments_data[0].type)) {
                    return this.view(row.attachments_data[0].url, row.attachments_data[0].name);
                }
                return this.errorFileFormat();
            }
        }
    },
}
</script>
