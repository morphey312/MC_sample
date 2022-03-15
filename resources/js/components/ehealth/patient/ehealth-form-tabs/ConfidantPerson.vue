<template>
    <div>
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-select
                        :entity="model"
                        options="ehealth_confidant_person_type"
                        property="relation_type"
                        :label="__('Тип связи з пациентом')"
                    />
                    <form-input
                        :entity="model"
                        property="last_name"
                        :label="__('Фамилия')"
                    />
                    <form-input
                        :entity="model"
                        property="first_name"
                        :label="__('Имя')"
                    />
                    <form-input
                        :entity="model"
                        property="second_name"
                        :label="__('Отчество')"
                    />
                    <form-input
                        :entity="model"
                        property="tax_id"
                        :label="__('ИНН')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="birth_country"
                        :label="__('Страна рождения')"
                    />
                    <form-input
                        :entity="model"
                        property="birth_settlement"
                        :label="__('Место рождения')"
                    />
                    <form-date
                        :entity="model"
                        property="birth_date"
                        :label="__('Дата рождения')"
                    />
                    <form-switch
                        :entity="model"
                        options="gender"
                        property="gender"
                        :label="__('Пол')"
                    />
                    <form-input
                        :entity="model"
                        property="unzr"
                        :label="__('Уникальный номер записи в ЭДДР')"
                    />
                </el-col>
                <el-col :span="8">
                    <div class="form-input-group">
                        <form-input
                            :entity="model"
                            property="phone_number"
                            :label="__('Номер телефона')"
                        />
                        <form-select
                            :entity="model"
                            options="ehealth_phone_type"
                            property="phone_type"
                            :label="__('Тип номера')"
                        />
                    </div>
                    <form-input
                        :entity="model"
                        property="email"
                        label="Email"
                    />
                    <form-select
                        :entity="model"
                        options="preferred_way_communication"
                        property="preferred_way_communication"
                        :label="__('Предпочтительный тип связи')"
                    />
                    <form-input
                        :entity="model"
                        property="secret"
                        :label="__('Слово пароль')"
                    />
                </el-col>
            </el-row>
        </section>
        <hr />
        <section>
            <h3 Class="mt-0 mb-20">{{ __('Документы представителя') }}</h3>
            <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper mt-0">
                <el-tab-pane
                    :lazy="true"
                    :label="__('Документы, удостоверяющие личность представителя')"
                    name="documents" >
                    <documents
                        :patient="model"/>
                </el-tab-pane>
                <el-tab-pane
                    :lazy="true"
                    :label="__('Документы, удостоверяющие представительство')"
                    name="cards" >
                    <relationship-documents
                        :patient="model"/>
                </el-tab-pane>
            </el-tabs>
        </section>
    </div>
</template>

<script>
import Documents from '@/components/ehealth/patient/ehealth-form-tabs/documents/List.vue';
import RelationshipDocuments from '@/components/ehealth/patient/ehealth-form-tabs/relationship-documents/List.vue';

export default {
    props: {
        model: {
            type: Object
        },
    },
    components: {
        Documents,
        RelationshipDocuments,
    },
    data() {
        return {
            activeTab: 'documents',
        }
    },

}
</script>
