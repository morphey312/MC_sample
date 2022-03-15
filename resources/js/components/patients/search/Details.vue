<template>
        <div class="has-icon">
            <span class="ellipsis">
                <a href="#" @click.prevent="selected">
                    {{ model.full_name }}
                </a>    
            </span>
            <el-tooltip 
                v-if="hasSkkOrBlackMark"
                placement="bottom" 
                effect="light" 
                :open-delay="500"
                popper-class="light-popover-content patient-warning pl-0 pr-0">
                <template slot="content">
                    <template v-if="hasBlackMark">
                        <div class="pl-10 pr-10">
                            <b>{{ __('Черная метка') }}</b>
                            <p>{{ model.black_mark_comment }}</p>
                        </div>
                        <hr class="mb-10">
                    </template>
                     <div class="pl-10 pr-10">
                        <b>{{ __('Примечание СКК') }}</b>
                        <p>{{ model.skk_comment }}</p>
                    </div>
                </template>
                <svg-icon 
                    name="info-alt" 
                    class="icon-tiny"
                    :class="warningColor" />
            </el-tooltip>
            <context-menu>
                <a 
                    v-if="$canUpdate('patients')"
                    href="#"
                    @click.prevent="editPatient">
                    {{ __('Данные пациента') }}
                </a>
                <a 
                    v-if="$can('patient-cabinet.access')"
                    href="#"
                    @click.prevent="goCabinet">
                    {{ __('Личный кабинет пациента') }}
                </a>
                <a 
                    v-if="$can('action-logs.access')"
                    href="#"
                    @click.prevent="showLogs">
                    {{ __('Операции') }}
                </a>
            </context-menu>
        </div>
</template>

<script>
import PatientLog from '@/components/action-log/Patient.vue';

export default {
    props: {
        model: Object,
    },
    computed: {
        hasSkk() {
            return this.model.is_skk;
        },
        hasBlackMark() {
            return this.model.black_mark;
        },
        hasSkkOrBlackMark() {
            return this.hasSkk || this.hasBlackMark;
        },
        warningColor() {
            if(this.hasSkk) {
                return this.hasBlackMark ? 'icon-black' : 'icon-red';
            }

            return 'icon-black';
        },
    },
    methods: {
        selected() {
            this.$emit('selected', this.model);
        },
        editPatient() {
            this.displayEditPatientForm(this.model.id, (patient) => {
                this.model = patient;
            });
        },
        goCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.model.id}});
            window.open(routeData.href, '_blank');
        },
        showLogs() {
            this.$modalComponent(PatientLog, {
                id: this.model.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения данных пациента'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}   
</script>