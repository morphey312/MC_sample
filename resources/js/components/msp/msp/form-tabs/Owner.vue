<template>
    <div class="owner-info">
        <error-catcher :catch="documentsErrors" />
        <section>
            <section 
                v-if="model.isNew()"
                class="form-header text-right">
                <el-button
                    @click="selectExisting">
                    {{ __('Выбрать из существующих сотрудников') }}
                </el-button>
                <el-button
                    :disabled="model.owner.isNew()"
                    @click="clearExisting">
                    {{ __('Создать нового') }}
                </el-button>
            </section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="owner.last_name"
                        :label="__('Фамилия')" />
                    <form-input
                        :entity="model"
                        property="owner.first_name"
                        :label="__('Имя')" />
                    <form-input
                        :entity="model"
                        property="owner.middle_name"
                        :label="__('Отчество')" />
                    <form-switch
                        :entity="model"
                        options="gender"
                        property="owner.gender"
                        :label="__('Пол')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="owner.phone"
                        :label="__('Номер телефона')" />
                    <form-input
                        :entity="model"
                        property="owner.additional_phone"
                        :label="__('Дополнительный номер телефона')" />
                    <form-input
                        :entity="model"
                        property="owner.email"
                        :label="__('E-mail')" />
                    <form-input
                        v-if="model.owner.isNew()"
                        :entity="model"
                        property="owner.user.login"
                        :label="__('Логин для входа в МЦ+')" />
                </el-col>
                <el-col :span="8">
                    <form-date
                        :entity="model"
                        :disabled="ownerCommited"
                        property="owner.birth_date"
                        :label="__('Дата рождения')" />
                    <form-input
                        :entity="model"
                        :disabled="ownerCommited"
                        property="owner.tax_id"
                        :label="__('РНУКПН')" />
                    <form-select
                        :entity="model"
                        property="owner_position"
                        :options="positions"
                        :label="__('Должность')" />
                    <form-input
                        v-if="model.owner.isNew()"
                        :entity="model"
                        type="password"
                        property="owner.user.password"
                        :label="__('Пароль для входа в МЦ+')" />
                </el-col>
            </el-row>
        </section>
        <hr />
        <documents 
            :key="documentsKey"
            :employee="model.owner" />
    </div>
</template>

<script>
import Documents from './documents/List.vue';
import MspOwner from '@/models/msp/owner';
import EmployeeSearch from '@/components/resources/employee/search-modal/Search.vue';
import EhealthPositionRepository from '@/repositories/ehealth/position';

export default {
    components: {
        Documents,
    },
    props: {
        model: Object,
    },
    computed: {
        documentsKey() {
            return this.model.owner.id;
        },
        ownerCommited() {
            return _.isFilled(this.model.owner.ehealth_id);
        }
    },
    data() {
        return {
            positions: [],
            documentsErrors: [
                {key: new RegExp('owner[.]documents[.][0-9]+[.]number'), label: __('Номер документа')},
                {key: new RegExp('owner[.]documents[.][0-9]+[.]issued_by'), label: __('Кем выдан документ')},
            ],
        };
    },
    mounted() {
        let repository = new EhealthPositionRepository({
            filters: {is_owner: 1},
        });
        repository.fetchList().then((list) => {
            this.positions = list.map(item => ({id: item.code, value: item.value}));
        });
    },
    methods: {
        selectExisting() {
            this.$modalComponent(EmployeeSearch, {
                'scopes': ['default', 'documents'],
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, employee) => {
                    this.model.owner = new MspOwner(employee.attributes);
                    dialog.close();
                },
            }, {
                header: __('Выбор сотрудника'),
                width: '900px',
            });
        },
        clearExisting() {
            this.model.owner = new MspOwner();
        },
    }
}
</script>