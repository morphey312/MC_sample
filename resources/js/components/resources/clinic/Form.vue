<template>
    <model-form :model="model">
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Общее')"
                name="info" >
                <section>
                    <tab-info
                        :msp-list="mspList"
                        :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :disabled="!$can('medicine-stores.access')"
                :label="__('Медицинские склады')"
                name="medicine-stores" >
                <section>
                    <medicine-stores :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Бланки для печати')"
                name="clinic-blanks" >
                <section>
                    <clinic-blanks :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Реквизиты')"
                name="official" >
                <section>
                    <official
                        :model="model"
                        :msp-type="mspType" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('service-types') && !model.isNew() && canHaveServices"
                :lazy="true"
                :label="__('Виды услуг')"
                name="service-types" >
                <service-types
                    :modal-component="modalComponent"
                    :clinic="model"
                    :msp-type="mspType" />
            </el-tab-pane>
            <el-tab-pane
                :label="__('Настройки клиники')"
                name="settings" >
                <section>
                    <settings
                        :model="model"
                    />
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import TabInfo from './form-tabs/Info.vue';
import MedicineStores from './form-tabs/MedicineStores.vue';
import ClinicBlanks from './form-tabs/Blanks.vue';
import Official from './form-tabs/Official.vue';
import Settings from './form-tabs/Settings.vue';
import ServiceTypes from './form-tabs/ServiceTypes.vue';
import CONSTANT from '@/constants';
import MspRepository from '@/repositories/msp';

export default {
    components: {
        TabInfo,
        MedicineStores,
        ClinicBlanks,
        Official,
        ServiceTypes,
        Settings
    },
    props: {
        model: {
            type: Object
        },
        modalComponent: Object,
    },
    computed: {
        canHaveServices() {
            return this.model.active_in_ehealth && [
                CONSTANT.EHEALTH.MSP_TYPE.OUTPATIENT,
                CONSTANT.EHEALTH.MSP_TYPE.EMERGENCY,
                CONSTANT.EHEALTH.MSP_TYPE.PRIMARY_CARE,
            ].indexOf(this.mspType) !== -1;
        }
    },
    data() {
        return {
            activeTab: 'info',
            mspType: this.model.get('msp_type'),
            mspList: [],
        };
    },
    mounted() {
        this.getMspList();
    },
    methods: {
        getMspList() {
            let repository = new MspRepository();
            repository.fetchList().then((list) => {
                this.mspList = list;
            });
        },
        getMspType(val) {
            if (val) {
                let msp = _.findById(this.mspList, val);
                this.mspType = msp === undefined ? null : msp.type;
            } else {
                this.mspType = null;
            }
        },
    },
    watch: {
        ['model.msp_id'](val) {
            this.getMspType(val);
        }
    },
};
</script>
