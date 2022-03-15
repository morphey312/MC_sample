<template>
    <manage-table
        v-if="takeId.length !== 0"
        ref="table"
        :fields="fields"
        :repository="repository"
        :show-table-settings="false"
        :enable-pagination="false">
        <template
            slot="result"
            slot-scope="props">
            <a
                v-if="hasResult(props.rowData.protocol)"
                href="#"
                @click.prevent="view(getResultUrl(props.rowData.protocol))">
                {{ __('Показать') }}
            </a>
            <a
                v-else
                href="#"
                @click.prevent="select(props.rowData.protocol)">
                {{ __('Заполнить') }}
            </a>
        </template>
        <template
            slot="attach"
            slot-scope="props">
            <a
                v-if="hasResult(props.rowData.protocol)"
                href="#"
                @click.prevent="view(getResultUrl(props.rowData.protocol))">
                {{ __('Показать') }}
            </a>
            <a
                v-else
                href="#"
                @click.prevent="attach(props.rowData.protocol)">
                {{ __('Прикрепить') }}
            </a>
        </template>
        <template
            slot="edit"
            slot-scope="props" >
            <span
                @click="edit(props.rowData.protocol)">
                <svg-icon
                    name="edit"
                    :disabled="!hasResult(props.rowData.protocol)"
                    class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
    <div v-else>
        {{ __('В записи на прием нет услуг по выбранной специализации') }}
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import ServiceRepository from '@/repositories/service';
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        specialization: [Number, String],
        takeId: Array,
        protocols: Array,
        appointment: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                let repository = new ServiceRepository();
                return repository.fetch({
                    id: this.takeId,
                    specialization: this.specialization,
                    has_protocol_clinic: this.appointment.clinic_id,
                }, [], ['protocol_templates'], 1, 1000).then((result) => {
                    let list = [];
                    result.rows.forEach((service) => {
                        service.protocol_templates.forEach((protocol) => {
                            if (protocol.clinics.indexOf(this.appointment.clinic_id) !== -1) {
                                list.push({
                                    service,
                                    protocol,
                                });
                            }
                        });
                    });
                    return {rows: list};
                });
            }),
            fields: [
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                },
                {
                    name: 'protocol.name',
                    title: __('Название протокола'),
                    width: '250px',
                },
                {
                    name: 'result',
                    title: __('Результат'),
                    width: '120px',
                },
                {
                    name: 'attach',
                    title: __('Прикрепить документ'),
                    width: '120px',
                },
                {
                    name: 'edit',
                    title: ' ',
                    width: '30px',
                    dataClass: 'no-ellipsis',
                },
            ],
        };
    },
    methods: {
        select(protocol) {
            this.$emit('select', protocol);
        },
        edit(protocol) {
            protocol.result = this.getResult(protocol);

            if(protocol.result) {
                this.$emit('edit', protocol);
            }
        },
        attach(protocol) {
            this.$emit('attach', protocol);
        },
        hasResult(protocol) {
            return this.getResult(protocol) !== undefined;
        },
        getResult(protocol) {
            return _.find(this.protocols, (result) => result.template_id == protocol.id);
        },
        getResultUrl(protocol) {
            return this.getResult(protocol).file_data.url;
        },
    },
    watch: {
        takeId(val) {
            this.$refs.table.refresh();
        }
    },
};
</script>
