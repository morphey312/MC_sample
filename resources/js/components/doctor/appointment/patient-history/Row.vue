<template>
    <div>
        <a
            href="#"
            :title="$formatter.datetimeFormat(row.date)"
            @click.prevent="clicked"
        >
            <span v-if="isDocument(row.type)">
                {{ title }} {{ '(' + getFullName(row.doctor) + ')' }}
            </span>
            <span v-else-if="isPrintedDocument(row.type)">
                {{ title }} ({{ getFullName(row.doctor) }})
            </span>
            <span v-else>
                {{ title }}: {{ getFullName(row.doctor) }}
            </span>

            <svg-icon
                v-if="isAssignedMedicine(row)"
                name="info-alt"
                class="icon-tiny icon-grey"
                :title="__('Операции')"
                @click.stop="showMedicineLog(row)" />
        </a>
    </div>
</template>
<script>
import CONSTANTS from '@/constants';
import MedicineHistoryLog from "@/components/action-log/patient/MedicineHistory.vue";

export default {
    props: {
        row: Object,
        titleList: Object,
    },
    computed: {
        title() {
            let type = this.row.type;
            if (type === CONSTANTS.CARD_RECORD.TYPE.CARD_ASSIGNMENT) {
                return this.titleList[type][this.row.recordable.type];
            } else if (type === CONSTANTS.CARD_RECORD.TYPE.TREATMENT_ASSIGNMENT) {
                if (this.row.recordable.initial == 1) {
                    return this.titleList[type].initial;
                } else {
                    return this.titleList[type].repeated;
                }
            } else if (this.isDocument(type)) {
                return this.titleList[type] + ' ' + this.getDocumentTitle();
            } else if (this.isPrintedDocument(type)) {
                return this.titleList[type] + ' ' + this.getPrintedDocumentTitle(this.row) ;
            }
            else {
                if (this.titleList[type] != undefined) {
                    return this.titleList[type]
                } else {
                    return this.titleList.default;
                }
            }
        },
    },
    methods: {
        clicked() {
            this.$emit('show-details', this.row);
        },
        isDocument(type) {
            return type === CONSTANTS.CARD_RECORD.TYPE.PATIENT_DOCUMENT
                || type === CONSTANTS.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD;
        },
        isAssignedMedicine(row){
            return row.type === CONSTANTS.CARD_RECORD.TYPE.CARD_ASSIGNMENT &&
            row.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES;
        },
        showMedicineLog(row){
            this.$modalComponent(MedicineHistoryLog, {
                id: row.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения назначений медикаментов'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        isPrintedDocument(type) {
            return type === CONSTANTS.CARD_RECORD.TYPE.PRINTED_DOCUMENT;
        },
        getDocumentTitle() {
            if (this.row.attachments_data.length === 0) {
                return '';
            }
            return this.$formatter.listFormat(this.row.attachments_data, 'name');
        },
        getPrintedDocumentTitle(data){
            let document_types = {
                PrintAdvisory: __('Консультативное заключение'),
                PrintFile: data.file ? data.file.name : ''
            };
            return document_types[this.row.document_name];
        },
        getFullName(doctor) {
            if (doctor.full_name !== undefined) {
                return doctor.full_name;
            }
            return [
                doctor.last_name,
                doctor.first_name,
                doctor.middle_name,
            ]
                .filter(_.isFilled)
                .join(' ');
        }
    },
}
</script>
