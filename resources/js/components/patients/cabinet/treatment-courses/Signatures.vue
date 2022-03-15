<template>
    <manage-table
        v-loading="loading"
        :fields="fields"
        :repository="repository"
        :enable-pagination="false">
        <template
            slot="download"
            slot-scope="props" >
            <a 
                href="#"
                @click.prevent="download(props.rowData)">{{ __('Загрузить') }}</a>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import fileLoader from '@/services/file-loader';

export default {
    props: {
        document: {
            type: Object,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.document.signatures,
                });
            }),
            fields: [
                {
                    name: 'employee_name',
                    title: __('Подписант'),
                },
                {
                    name: 'date',
                    title: __('Дата подписания'),
                    width: '25%',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    }
                },
                {
                    name: 'download',
                    title: __('Подписаный файл'),
                    width: '25%',
                },
            ],
            loading: false,
        };
    },
    methods: {
        download(signature) {
            this.loading = true;
            fileLoader.get(signature.file.url).then((blobUrl) => {
                let link = document.createElement('a');
                link.href = blobUrl;
                link.download = signature.file.name;
                link.click();
                this.loading = false;
            });
        },
    }
}
</script>