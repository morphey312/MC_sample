<template>
    <div>
        <p>{{ __('Выберите специализацию, по которой вы будете вести прием.') }}</p>
        <manage-table 
            :fields="fields"
            :repository="repository"
            :enable-loader="false">
            <template 
                slot="action" 
                slot-scope="props">
                <a 
                    href="#"
                    @click.prevent="$emit('selected', props.rowData.id)">
                    {{ __('Выбрать') }}
                </a>
            </template>
        </manage-table>
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        list: Array,
    },
    data() {
        return {
            fields: [
                {
                    name: 'value',
                    title: __('Специализация'),
                    width: '75%',
                },
                {
                    name: 'action',
                    title: '',
                    width: '25%',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.list,
            })),
        };
    },
};
</script>