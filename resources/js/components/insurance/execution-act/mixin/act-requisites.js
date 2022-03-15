import Clinic from '@/models/clinic';
import InsuranceCompany from '@/models/insurance-company';
import ClinicRepository from "@/repositories/clinic";
import InsuranceCompanyRepository from "@/repositories/insurance-company";

export default {
    methods: {
        getRequisites(clinicId, insurerId) {
            let clinic = new Clinic({id: clinicId});
            let insuranceCompany = new InsuranceCompany({id: insurerId});
            return Promise.all([
                clinic.fetch(),
                insuranceCompany.fetch(),
            ]).then(() => {
                return Promise.resolve({clinic, insurer: insuranceCompany});
            });
        },
        exportAct() {
            this.getActServices();
            this.loading = true;
            let rows = [];
            let makeAct = async () => {
                rows = await this.getActServices();
                if (rows.length === 0) {
                    this.loading = false;
                    return this.$error(__('Данные для формирования акта отсутствуют'));
                }

                let requisites = await this.getRequisites(this.activeItem.clinic_id, this.activeItem.insurance_company_id);
                requisites.insuranceAct = this.activeItem;

                actGenerator(rows, requisites).then(({book, amount}) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        FileSaver.saveAs(new Blob([buffer]), __('Акт выполненных работ (страховые)') + '.xlsx');
                        this.loading = false;
                        this.activeItem.printed();
                    }).catch((err) => {
                        console.error(err);
                        this.$error(__('Не удалось сохранить файл'));
                        this.loading = false;
                    });
                });
            };
            makeAct();
        },
    }
}
