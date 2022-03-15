<template>
    <registrations-list 
        ref="table"
        :filters="initialFilters"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canAccess('patients')"
                :disabled="activeItem === null"
                @click="findPatient">
                {{ __('Найти пациента') }}
            </el-button>
        </div>
    </registrations-list>
</template>

<script>
import RegistrationsList from './registrations/List.vue';
import ManageMixin from '@/mixins/manage';
import SearchPatient from '@/components/patients/search/Search.vue';
import RegistrationMixin from './mixins/registration';

export default {
    mixins: [
        ManageMixin,
        RegistrationMixin,
    ],
    components: {
        RegistrationsList,
    },
    props: {
        initialFilters: Object,
    },
    methods: {
        findPatient() {
            let registration = this.activeItem;
            this.$modalComponent(SearchPatient, {
                customCreateFunction: (done) => {
                    done(false);
                    this.createAndBind(registration);
                },
                filterDefaults: () => {
                    return {
                        primary_phone_number: `=${registration.phone}`,
                    };
                },
                autoSearch: true,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    this.bindExisting(registration, patient);
                    dialog.close();
                },
            }, {
                header: __('Привязка пациента'),
                width: '1270px',
            });
        },
        createAndBind(registration) {
            this.displayCreatePatientForm(() => {
                this.refresh();
            }, false, {
                firstname: registration.firstname,
                lastname: registration.lastname,
                middlename: registration.middlename,
                phone: registration.phone,
                email: registration.email,
                birthday: registration.birthday,
                registration_id: registration.id,
            });
        },
        bindExisting(registration, patient) {
            this.displayEditPatientForm(patient.id, 
                (patient) => {
                    this.refresh();
                },
                false,
                {
                    afterFetch: (patient) => {
                        this.extendPatientData(registration, patient);
                    },
                }
            );
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
}
</script>