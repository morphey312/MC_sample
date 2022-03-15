<template>
    <el-row :gutter="20">
        <el-col :span="8">
            <form-input
                :entity="model"
                property="name"
                :label="__('Название')" />
            <form-checkbox
                :entity="model"
                property="cant_be_copied"
                :label="__('Нельзя передавать от владельца другим лицам')" /> 
            <form-checkbox
                :entity="model"
                property="show_card_in_patient_list"
                :label="__('Показывать наличие карты у пациента в списке контактных лиц')" />  
            <form-row
                name="type_icon_id"
                :label="__('Иконка для карт')">
                <el-select 
                    v-model="model.type_icon_id"
                    :disabled="!model.show_card_in_patient_list">
                    <el-option
                        v-for="icon in icons"
                        :key="icon.id"
                        :label="icon.value"
                        :value="icon.id">
                        <span class="float-left">{{ icon.value }}</span>
                        <span 
                            v-if="icon.picture"
                            class="float-right el-select-icon">
                            <img :src="icon.picture" />
                        </span>
                    </el-option>
                </el-select>
            </form-row>
        </el-col>
        <el-col :span="8">
            <form-input
                :entity="model"
                property="discount_percent"
                type="number"
                :min="0"
                :max="100"
                :step="1"
                :disabled="model.use_detail_payments"
                :label="__('Процент скидки')" />
            <form-checkbox
                :entity="model"
                property="use_detail_payments"
                :label="__('Детализация по назначениям платежей')" />  
            <form-checkbox
                :entity="model"
                property="use_card_number"
                class="aligned-checkbox"
                :label="__('Использовать номер для карт')" />
            <form-select 
                :entity="model"
                :options="numberingKinds"
                :disabled="!model.use_card_number"
                class="pt-10"
                property="number_kind_id"
                :label="__('Вид автоматической нумерации')"
            />
        </el-col>
        <el-col :span="8">
             <form-input
                :entity="model"
                property="expire_period"
                type="number"
                :min="1"
                :step="1"
                :label="__('Период действия')" />
            <form-checkbox
                :entity="model"
                property="dont_use_for_patient"
                :label="__('Не использовать для выдачи пациентам')" /> 
            <form-checkbox
                :entity="model"
                property="dont_auto_add_to_appointment"
                class="aligned-checkbox"
                :label="__('Не добавлять автоматически в запись')" />    
            <form-input
                :entity="model"
                property="priority"
                type="number"
                class="pt-10"
                :min="1"
                :step="1"
                :label="__('Приоритет карт для услуг/анализов')" />
        </el-col>
    </el-row>
</template>

<script>
import IconRepository from '@/repositories/discount-card-type/icon';
import NumberingKindRepository from '@/repositories/discount-card-type/numbering-kind';
import fileLoader from '@/services/file-loader';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            icons: [],
            numberingKinds: new NumberingKindRepository(),
        };
    },
    beforeMount() {
        this.getIcons();
    },
    beforeDestroy() {
        fileLoader.revokeAll();
    },
    methods: {
        getIcons() {
            let icon = new IconRepository();
            icon.fetchList().then((response) => {
                if (response.length !== 0) {
                    this.castIcons(response);
                }
            });
        },
        castIcons(list) {
            list.forEach((image) => {
                if (image.attachments_data.length !== 0) {
                    this.getImage(image.attachments_data[0].url)
                        .then((url) => {
                            image.picture = url;
                            this.icons.push(image);
                        });
                } else {
                    this.icons.push(image);
                }
            });
        },
        getImage(url) {
            return fileLoader.get(url);
        },
    }
}
</script>
