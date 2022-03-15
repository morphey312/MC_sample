<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название')" />
                    </el-col>
                    <el-col :span="12">
                        <form-row
                            name="card_unique"
                            label="&nbsp">
                            <form-checkbox
                                :entity="model"
                                property="unique"
                                :label="__('Можно поставить только уникальный номер карты')" />
                        </form-row>
                    </el-col>
                </el-row>
            </section>
            <section>
                <form-row name="clinics">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="160px"
                        :right-title="__('Клиника')"
                        right-width="160px"
                        @new-row="initClinic">
                        <template slot="numbering_type" slot-scope="props">
                            <form-select
                                :entity="props.rowData.data"
                                options="card_numbering_type"
                                property="numbering_type"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                        <template slot="start_number" slot-scope="props">
                            <form-input
                                :entity="props.rowData.data"
                                property="start_number"
                                type="number"
                                :min="0"
                                :step="1"
                                control-size="mini"
                                css-class="table-row text-right" />
                        </template>
                        <template slot="prefix" slot-scope="props">
                            <form-input
                                :entity="props.rowData.data"
                                property="prefix"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                        <template slot="suffix" slot-scope="props">
                            <form-input
                                :entity="props.rowData.data"
                                property="suffix"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons" />
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
export default {
    props: {
        model: Object
    },
    data() {
        return {
            placeholder: this.getPlaceholder(),
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('card-numbering-kinds')
            }),
            clinicFields: [
                {
                    name: 'numbering_type',
                    title: __('Тип нумерации'),
                    width: '140px',
                },
                {
                    name: 'start_number',
                    title: __('Номер начинается с'),
                    width: '130px',
                },
                {
                    name: 'prefix',
                    title: __('Префикс'),
                    width: '80px',
                },
                {
                    name: 'suffix',
                    title: __('Суффикс'),
                    width: '80px',
                },
            ],
        }
    },
    methods: {
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Добавьте клиники слева'));
        },
        initClinic(data) {
            data.start_number = 0;
            let types = this.$handbook.getOptions('card_numbering_type');

            if(types.length != 0){
                data.numbering_type = types[0].id;
            }
        },
    },
}
</script>
