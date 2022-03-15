import PrintedDocument from "@/models/patient/card/printed-document";
import CONSTANTS from '@/constants';

export default {
    methods: {
        logPrint(patientDocument) {
            if (this.appointment.specialization_card) {
                let model = new PrintedDocument({
                    card_specialization_id: this.appointment.specialization_card.id,
                    appointment_id: this.appointment.id || null,
                    document_name: CONSTANTS.PRINT_LOG.FILE,
                    file_id: patientDocument.file_id
                });

                model.save();
            }
        },
    }
}