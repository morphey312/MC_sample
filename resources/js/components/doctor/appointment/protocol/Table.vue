<template>
    <div 
        v-if="protocols.length !== 0"
        class="protocols-block paragraph">
        <h3 v-if="title">{{ title }}</h3>
        <manage-table
            v-loading="deleting"
            ref="table"
            :fields="fields"
            :repository="repository">
            <template
                slot="name"
                slot-scope="props" >
                <a
                    href="#"
                    @click.prevent="view(props.rowData.file_data.url)">
                    {{ props.rowData.template_name }}
                </a>
            </template>
            <template
                slot="remove"
                slot-scope="props" >
                <span class="" @click="remove(props.rowData)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </span>
            </template>
        </manage-table>
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        protocols: Array,
        title: {
            type: [Boolean, String],
            default: __('Протоколы исследований'),
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
                    rows: this.protocols,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название протокола'),
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: ' ',
                    width: '32px'
                }]),
            ],
            deleting: false,
        };
    },
    methods: {
        remove(protocol) {
            this.$confirm(__('Вы действительно хотите удалить этот протокол исследования?'), () => {
                this.deleting = true;
                protocol.delete().then(() => {
                    this.$info(__('Протокол исследования был успешно удален'));
                    _.pull(this.protocols, protocol);
                    this.refresh();
                }).finally(() => {
                    this.deleting = false;
                });
            });
        },
        refresh() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    watch: {
        protocols() {
            this.refresh();
        },
    },
}   
</script>