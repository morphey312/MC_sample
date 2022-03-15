<template>
    <el-tabs 
        v-model="activeTab" 
        class="tab-group-beige sections-wrapper request-log">
        <el-tab-pane
            :label="__('Сводные данные')"
            name="info">
            <alert 
                v-if="isSuccess"
                type="info">
                {{ __('Запрос успешно выполнен') }}
            </alert>
            <alert 
                v-else
                type="attention">
                {{ errorDescription }}
            </alert>
            <section>
                <manage-table 
                    :fields="fields"
                    :repository="repository"
                    :enable-loader="false">
                </manage-table>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Технические детали')"
            name="raw">
            <section>
                <h3>{{ __('Данные запроса') }}</h3>
                <pre class="text-select" v-html="json(item.request_data || item.request)"></pre>
            </section>
            <section class="grey">
                <h3>{{ __('Данные ответа') }}</h3>
                <pre class="text-select" v-html="json(item.response)"></pre>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        item: Object,
    },
    data() {
        return {
            activeTab: 'info',
            fields: [
                {
                    name: 'key',
                    title: __('Свойство'),
                    width: '30%',
                },
                {
                    name: 'value',
                    title: __('Значение'),
                    width: '30%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'error',
                    title: __('Ошибка'),
                    dataClass: 'no-ellipsis',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.listProps(this.item.request_data || this.item.request),
            })),
        };
    },
    computed: {
        errorDescription() {
            switch (this.responseCode) {
                case 403: return __('Нет прав на выполнение данного запроса'); 
                case 404: return __('Не найден ресурс'); 
                case 409: return __('Несоответствие данных');
                case 422: return __('Данный запрос не прошел валидацию'); 
                default: return __('Ошибка на сервере');
            }
        },
        isSuccess() {
            return this.responseCode === 200 || this.responseCode === 201;
        },
        responseCode() {
            return this.item.response.meta === undefined ? -1 : this.item.response.meta.code;
        }
    },
    methods: {
        json(data) {
            return JSON.stringify(data, null, '  ');
        },
        listProps(data) {
            let props = [];
            this.flatten(data, props, '');
            return props;
        },
        flatten(data, props, prefix) {
            Object.keys(data).forEach((key) => {
                this.exposeValue(prefix + key, data[key], props);
            });
        },
        exposeValue(key, value, props) {
            if (value instanceof Array) {
                value.forEach((item, index) => {
                    this.exposeValue(key + '.[' + index + ']', item, props);
                });
            } else if (value && (typeof value === 'object')) {
                this.flatten(value, props, key + '.');
            } else {
                props.push({
                    key,
                    value: String(value),
                    error: this.getError('$.' + key),
                });
            }
        },
        getError(key) {
            if (this.item.response.error && this.item.response.error.invalid) {
                let error = _.find(this.item.response.error.invalid, (item) => item.entry === key);
                if (error !== undefined) {
                    return error.rules.map(r => r.description).join('; ');
                }
            }
            return null;
        },
    }
}
</script>