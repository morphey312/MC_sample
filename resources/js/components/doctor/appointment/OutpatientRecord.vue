<template>
    <model-form
        v-if="structure !== null"
        :model="model"
        class="card-record-section">
        <section-wrapper
            v-if="ordinarySections.length != 0"
            :label="__('Амбулаторное обследование')">
            <template slot="button">
                <el-button
                    style="margin-top: 15px;margin-left: 10px;"
                    @click="clearOutpatientCard">
                    {{ __('Очистить') }}
                </el-button>
            </template>
            <div class="card-record-subsection">
                <h2>{{ __('Жалобы') }}</h2>
                <div class="card-record-line">
                    <div class="card-record-field growable" width="100%">
                        <el-input
                            v-model="model.complaints"
                            autosize
                            type="textarea" />
                    </div>
                </div>
            </div>
            <fields-section
                v-for="(section, index) in ordinarySections"
                :key="index"
                :label="section.label"
                :fields="section.children"
                @data-changed="dataChanged" />
            <div class="mt-20">
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            :multiple="true"
                            :repository="diagnosisRepository"
                            :collapse-tags="false"
                            property="diagnosis_icd"
                            :label="__('Диагноз по МКБ')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-text
                            :autosize="true"
                            :entity="model"
                            property="diagnosis"
                            :label="__('Диагноз')"
                        />
                    </el-col>
                </el-row>
            </div>
        </section-wrapper>
        <template v-if="extraSections.length != 0">
            <section-wrapper
                v-for="(section, index) in extraSections"
                :key="index"
                :label="section.label">
                <fields-section
                    :label="section.hint ? '' : section.label"
                    :fields="section.children"
                    @data-changed="dataChanged" />
            </section-wrapper>
        </template>
        <div class="text-right card-record-section"
            v-if="$can('doctor-cabinet.outpatient-records')">
            <el-button
                type="primary"
                @click="save">
                {{ __('Сохранить изменения') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
import SectionWrapper from './SectionWrapper.vue';
import OutpatientRecord, {NO_CHANGES} from '@/models/patient/card/outpatient-record';
import {Structure} from '@/services/card/template';
import DiagnosisRepository from '@/repositories/diagnosis';
import FieldsSection from './outpatient-record/Section.vue';
import lts from '@/services/lts';
import CONSTANT from '@/constants';

export default {
    components: {
        SectionWrapper,
        FieldsSection,
    },
    props: {
        model: Object,
        template: Object,
        appointment: Object,
        isCompleteDialog: Boolean,
        templateAddons: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            structure: null,
            diagnosisRepository: new DiagnosisRepository(),
            watchChanges: false,
        };
    },
    computed: {
        ordinarySections() {
            if (this.structure != null) {
                return this.structure.getSections().filter(section => !section.isExtra);
            }
            return [];
        },
        extraSections() {
            if (this.structure != null) {
                return this.structure.getSections().filter(section => section.isExtra);
            }
            return [];
        },
    },
    created() {
        this.processDataChange = _.debounce(() => {
            this.autoSave();
            this.$emit('data-changed');
        }, 1000);
    },
    mounted() {
        try {
            let structure = new Structure(this.template.structure, this.template.field_keys, this.templateAddons);
            this.model.mapStructure(structure);
            this.structure = structure;
            let autoSave = lts.outpatientRecord;
            let restored = false;
            if (autoSave && autoSave.appointment_id == this.model.appointment_id) {
                this.model.unserialize(autoSave);
                restored = true;
            }
            this.$nextTick(() => {
                this.watchChanges = true;
                if (restored) {
                    this.$emit('data-changed');
                }
            });
        } catch (e) {
            console.log(e);
            this.$error(e);
        }
    },
    methods: {
        clearOutpatientCard() {
            this.$emit('clear-outpatient-card');
        },
        save() {
            this.$clearErrors();
            this.watchChanges = false;
            let shouldPatch = this.shouldPatch(this.model, this.appointment);
            let promise = shouldPatch ? this.patchData() : this.saveData();
            promise.then((response) => {
                delete lts.outpatientRecord;
                this.$emit('data-saved');
                this.$nextTick(() => {
                    this.watchChanges = true;
                });
            }).catch((e) => {
                this.watchChanges = true;
                if (e === NO_CHANGES) {
                    this.$warning(__('Не было сделано изменений в карте'));
                } else {
                    this.$displayErrors(e);
                }
            });
        },
        saveData() {
            return this.model.save().then((response) => {
                this.model.setPrevious();
                this.model.mapStructure(this.structure);
                this.$info(__('Изменения были успешно сохранены'));
            });
        },
        patchData() {
            return this.model.patch().then((response) => {
                this.model.setPrevious();
                this.model.mapStructure(this.structure);
                this.$info(__('Изменения были успешно сохранены'));
            });
        },
        shouldPatch(model, appointment) {
            let isAppointmentEnded = [
                CONSTANT.APPOINTMENT.STATUSES.SIGNED_UP,
                CONSTANT.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
                CONSTANT.APPOINTMENT.STATUSES.CAME_TO_DOCTOR,
            ].indexOf(appointment.status.system_status) === -1;
            return (!model.isNew() && isAppointmentEnded);
        },
        dataChanged() {
            if (this.watchChanges && !this.isCompleteDialog) {
                this.processDataChange();
            }
        },
        autoSave() {
            lts.outpatientRecord = this.model.serialize();
        },
        setDiagnosisNames() {
            if (this.model.diagnosis_icd.length === 0) {
                this.model.diagnosis_icd_names = [];
            } else {
                this.diagnosisRepository.fetchList({id: this.model.diagnosis_icd}).then(response => {
                    this.model.diagnosis_icd_names = this.model.diagnosis_icd.map(item => {
                        return response.find(icd_name => icd_name.id === item).value
                    })
                })
            }
        }
    },
    watch: {
        ['model.diagnosis_icd'](val) {
            this.setDiagnosisNames();
            this.dataChanged();
        },
        ['model.diagnosis'](val) {
            this.model.diagnosis_erased = val === '';
            this.dataChanged();
        },
        ['model.complaints'](val) {
            this.dataChanged();
        },
    }
}
</script>
