<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-input
                    :entity="model"
                    :clearable="true"
                    :required="true"
                    property="caller"
                    :label="__('Кто вызывал')"
                />
                <form-checkbox
                    :entity="model"
                    property="is_patient"
                    :label="__('Пациент')"
                />
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="phone"
                    :label="__('Контактный номер телефона')"
                />
                <form-checkbox
                    :entity="model"
                    property="patient_secondary_phone"
                    :label="__('Сделать доп. номером пациента')"
                />
            </el-col>
        </el-row>
        <hr class="mt-10 mb-10" />
        <el-row :gutter="20">
            <el-col :span="24"> <h3 class="mb-20">Адрес выезда</h3> </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="districts"
                    :filterable="true"
                    property="district"
                    :label="__('Район')"
                />
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            :required="true"
                            property="house"
                            :label="__('Дом')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="porch"
                            :label="__('Подъезд')"
                        />
                    </el-col>
                    <el-col :span="24">
                        <form-checkbox
                            :entity="model"
                            class="mt-20"
                            property="patient_home_address"
                            :label="__('Домашний адрес пациента')"
                        />
                    </el-col>
                </el-row>
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    :required="true"
                    property="street"
                    :label="__('Улица')"
                />
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="flat"
                            :label="__('Квартира')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="storey"
                            :label="__('Этаж')"
                        />
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
        <hr class="mt-10 mb-10" />
        <el-row :gutter="20">
            <el-col :span="24">
                <h3 class="mb-20">Дополнительная информация</h3>
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="reason"
                    :label="__('Повод вызова')"
                />
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="comment"
                    :label="__('Примечание')"
                />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object
        },
        appointment: Object,
        patient: Object
    },
    data() {
        return {
            districts: [
                { id: "Шевченковский", value: "Шевченковский" },
                { id: "Киевский", value: "Киевский" },
                { id: "Слободской", value: "Слободской" },
                { id: "Основянский", value: "Основянский" },
                { id: "Холодногорский", value: "Холодногорский" },
                { id: "Московский", value: "Московский" },
                { id: "Новобаварский", value: "Новобаварский" },
                { id: "Индустриальный", value: "Индустриальный" },
                { id: "Немышлянский", value: "Немышлянский" }
            ]
        };
    },
    watch: {
        ["model.is_patient"](val) {
            if (val && this.model.caller === "") {
                this.model.caller = __("Пациент");
            } else if (this.model.caller === __("Пациент")) {
                this.model.caller = "";
            }
        },
        ["model.patient_home_address"](val) {
            if (val && this.model.street === "") {
                this.model.street = this.patient.street;
            }
            if (val && this.model.house === "") {
                this.model.house = this.patient.house_number;
            }
            if (val && this.model.flat === "") {
                this.model.flat = this.patient.apartment_number;
            }
        },
    }
};
</script>
