<template>
    <page
        :title="__('Медикаментозное лечение: {name}', {name: patient.full_name})"
        type="flex"
    >
        <template slot="header-addon">
            <div class="buttons">
                <a
                    v-if="$can('patient-cabinet.issue-medicine')"
                    href="#"
                    @click.prevent="issueMedicines"
                >
                    <svg-icon
                        name="edit-alt"
                        class="icon-small icon-blue"
                    >
                        {{ __('Выдать медикаменты') }}
                    </svg-icon>
                </a>
                <a
                    href="#"
                    @click.prevent="showSignalRecord"
                >
                    <svg-icon
                        name="report-alt"
                        class="icon-small icon-blue"
                    >
                        {{ __('Сигнальные обозначения') }}
                    </svg-icon>
                </a>
            </div>
        </template>
        <el-tabs
            v-model="activeTab"
            class="tab-group-grey shrinkable-tabs"
        >
            <el-tab-pane
                :lazy="true"
                :label="__('Платные препараты')"
                name="paylist"
            >
                <section class="darkgrey-cap shrinkable pt-0">
                    <issued-pay-list
                        ref="payTable"
                        :patient="patient"
                        @selection-changed="setActiveItem"
                    >
                        <div
                            v-if="$can('action-logs.assigned-issued-meds')"
                            slot="buttons"
                            class="buttons"
                        >
                            <el-button
                                v-if="$can('action-logs.assigned-issued-meds')"
                                :disabled="activeItem === null"
                                @click="showLog(activeItem)"
                            >
                                {{ __('Операции') }}
                            </el-button>
                        </div>
                    </issued-pay-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Препараты в рамках курса лечения')"
                name="freelist"
            >
                <section class="darkgrey-cap shrinkable pt-0">
                    <issued-free-list
                        ref="freeTable"
                        :patient="patient"
                        @selection-changed="setActiveFreeItem"
                    >
                        <div
                            v-if="$can('action-logs.assigned-issued-meds')"
                            slot="buttons"
                            class="buttons"
                        >
                            <el-button
                                v-if="$can('action-logs.assigned-issued-meds')"
                                :disabled="activeFreeItem === null"
                                @click="showLog(activeFreeItem)"
                            >
                                {{ __('Операции') }}
                            </el-button>
                        </div>
                    </issued-free-list>
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import CabinetMixin from './mixins/cabinet';
import IssuedPayList from './issued-medicines/IssuedPayList.vue';
import IssuedFreeList from './issued-medicines/IssuedFreeList.vue';
import Assigned from './issued-medicines/Assigned.vue';
import AssignButton from './issued-medicines/AssignButton.vue';
import ManageMixin from '@/mixins/manage';
import AssignedMedicine from '@/components/action-log/AssignedMedicine.vue';

export default {
    components:{
        IssuedPayList,
        IssuedFreeList,
    },
    mixins: [
        CabinetMixin,
        ManageMixin
    ],
    data() {
        return {
            activeTab: 'paylist',
            activeFreeItem: null
        }
    },
    methods: {
        issueMedicines() {
            this.$modalComponent(Assigned, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                close: (dialog) => {
                    dialog.close();
                    this.refreshAll();
                },
            }, {
                header: __('Выдать медикаменты для:') + ' ' + this.patient.full_name,
                width: '1265px',
                headerAddon: {
                    component: AssignButton,
                    eventListeners: {
                        addMedicine: (dialog) => {
                            dialog.getTopComponent().addMedicine();
                        },
                    },
                },
            });
        },
        refreshAll() {
            let payTable = this.$refs.payTable;
            let freeTable = this.$refs.freeTable;
            if (payTable) {
                payTable.refresh();
            }
            if (freeTable) {
                freeTable.refresh();
            }
        },
        setActiveFreeItem(selection) {
            this.activeFreeItem = selection.length !== 0 ? selection[0] : null;
        },
        showLog(item) {
            this.$modalComponent(AssignedMedicine, {
                id: item.assigned_medicine_id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменений медикамента'),
                width: '900px',
                customClass: 'no-footer',
            });
        }
    },
}
</script>
