<template>
    <section class="sections-wrapper pb-20">
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            table-height="auto">
            <template
                slot="content"
                slot-scope="props" >
                <span :html="props.rowData.information" />
            </template>
        </manage-table>
    </section>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        record: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getRows();
            }),
            fields: [
                {
                    name: 'information',
                    title: __('Что изменилось'),
                },
            ],
        }
    },
    methods: {
        getRows() {
            let rows = this.record.fields.map((field) => {
                            return {
                                information: this.getFieldInformation(field),
                            }
                        });
            return Promise.resolve({rows});
        },
        getFieldInformation(field) {
            let optionLabels = '';
            let template = field.field_template
            let output = '<b>' + template.label.replace(':', '') + '</b>';
            output += ' ' + __('&ndash; Изменено:') + ' ';

            if (field.option_value !== null && field.option_value.length != 0) {
                if (_.isArray(field.option_value)) {
                    optionLabels = this.getLabels(template.options, field.option_value).join(', ');
                } else {
                    optionLabels = this.getOptionLabel(template.options, field.option_value);
                }
            }

            if (_.isFilled(field.value)) {
                output += (optionLabels ? (optionLabels + ', ') : '') + field.value;
            } else {
                output += optionLabels;
            }

            return output;
        },
        getLabels(options, keys) {
            let labels = [];
            keys.forEach(key => {
                labels.push(this.getOptionLabel(options, key));
            });
            return labels;
        },
        getOptionLabel(options, key) {
            let option = options.find(opt => opt.key == key);
            return option ? option.label : '';
        },
    }
}
</script>