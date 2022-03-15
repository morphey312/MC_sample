<template>
    <div>
        <section class="grey filter">
            <enquiries-filter
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section
            class="grey-cap"
            style="height: 400px; padding-bottom: 40px">
            <sticky-footer style="height: 100%">
                <enquiries-list
                    ref="table"
                    :filters="filters"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters" />
                <div slot="footer">
                    <el-button
                        v-if="$canUpdate('site-enquiries')"
                        :disabled="activeItem === null || !$canManage('site-enquiries.update', [activeItem.clinic_id])"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canProcessCalls()"
                        :disabled="activeItem === null"
                        @click="voipSelectContact">
                        {{ __('Задать пациента для звонка') }}
                    </el-button>
                    <el-button
                        v-if="$can('action-logs.access')"
                        @click="showLog"
                        :disabled="activeItem === null">
                        {{ __('Операции') }}
                    </el-button>
                </div>
            </sticky-footer>
        </section>
    </div>
</template>

<script>
import EnquiriesFilter from './Filter.vue';
import EnquiriesList from './List.vue';
import ManageMixin from '@/mixins/manage';
import FormEdit from './Edit.vue';
import SelectContactMixin from '../mixins/select-contact';
import SiteEnquiry from "@/components/action-log/SiteEnquiry";


export default {
    mixins: [
        ManageMixin,
        SelectContactMixin,
    ],
    components: {
        EnquiriesFilter,
        EnquiriesList,
    },
    methods: {
        getFilterUid() {
            return 'call-center-site-enquiries';
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить заявку'),
                width: '750px',
            };
        },
        getMessages() {
            return {
                updated: __('Заявка было успешно обновлена'),
            };
        },
        getDefaultFilters() {
            return {
                created_start: this.$moment().format('YYYY-MM-DD'),
                created_end: this.$moment().format('YYYY-MM-DD'),
            };
        },
        voipSelectContact() {
            if (this.activeItem.patient !== null) {
                this.selectPatientContact(this.activeItem.patient);
            } else {
                this.selectUnknownContact(this.activeItem.phone_number, this.activeItem.name);
            }
        },
        showLog() {
            this.$modalComponent(SiteEnquiry, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения заявки'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
