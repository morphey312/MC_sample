import CONSTANT from '@/constants';

export default {
    computed: {
        hasServiceTypes() {
            return _.isFilled(this.model.ehealth_id)
                && [
                    CONSTANT.EHEALTH.EMPLOYEE_TYPE.DOCTOR,
                    CONSTANT.EHEALTH.EMPLOYEE_TYPE.SPECIALIST,
                    CONSTANT.EHEALTH.EMPLOYEE_TYPE.PHARMACIST,
                    CONSTANT.EHEALTH.EMPLOYEE_TYPE.ASSISTANT,
                ].indexOf(this.model.ehealth_employee_type) !== -1;
        }
    },
    data() {
        return {
            clinicErrors: [
                {key: 'clinic', label: __('Клиника')},
                {key: 'clinic.start_date', label: __('Дата начала работы')},
                {key: 'clinic.position', label: __('Должность')},
                {key: 'clinic.employee_type', label: __('Тип сотрудника')},
            ],
            documentsErrors: [
                {key: 'documents', label: __('Документы')},
                {key: new RegExp('documents[.][0-9]+[.]number'), label: __('Номер документа')},
                {key: new RegExp('documents[.][0-9]+[.]issued_by'), label: __('Кем выдан документ')},
            ],
            educationErrors: [
                {key: 'doctor.educations', label: __('Образование')},
                {key: new RegExp('doctor[.]educations[.][0-9]+[.]city'), label: __('Город')},
                {key: new RegExp('doctor[.]educations[.][0-9]+[.]institution_name'), label: __('Название учреждения')},
                {key: new RegExp('doctor[.]educations[.][0-9]+[.]speciality'), label: __('Специальность')},
            ],
            qualificationErrors: [
                {key: new RegExp('doctor[.]qualifications[.][0-9]+[.]institution_name'), label: __('Название учреждения')},
                {key: new RegExp('doctor[.]qualifications[.][0-9]+[.]speciality'), label: __('Специальность')},
                {key: new RegExp('doctor[.]qualifications[.][0-9]+[.]additional_info'), label: __('Дополнительная информация')},
            ],
            specialityErrors: [
                {key: 'doctor.specialities', label: __('Специализации')},
                {key: new RegExp('doctor[.]specialities[.][0-9]+[.]attestation_name'), label: __('Учреждение, проводившее аттестацию')},
            ],
            degreeErrors: [
                {key: 'doctor.science_degree.city', label: __('Город')},
                {key: 'doctor.science_degree.institution_name', label: __('Учреждение')},
                {key: 'doctor.science_degree.speciality', label: __('Специальность')},
            ],
            activeTab: 'info',
            tabClinics: false,
            tabDocuments: false,
            tabEducations: false,
            tabQualifications: false,
            tabSpecialities: false,
            tabDegree: false,
            tabCheckboxCredentials: false,
        };
    },
    watch: {
        activeTab(val) {
            switch(val) {
                case 'clinics':
                    this.tabClinics = true;
                    break;
                case 'documents':
                    this.tabDocuments = true;
                    break;
                case 'educations':
                    this.tabEducations = true;
                    break;
                case 'qualifications':
                    this.tabQualifications = true;
                    break;
                case 'specialities':
                    this.tabSpecialities = true;
                    break;
                case 'science-degrees':
                    this.tabDegree = true;
                    break;
                case 'checkbox-credentials':
                    this.tabCheckboxCredentials = true;
                    break;
            }
        }
    }
}
