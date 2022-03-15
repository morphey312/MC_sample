import eventHub from '@/services/event-hub';
import handbook from '@/services/handbook';

import { bustCache } from '@/services/service-worker';

eventHub.$on('broadcast.record_changed', (data) => {
    let className = data.data.saved || data.data.deleted;

    switch (className) {
        case 'Analysis\\Laboratory':
            bustCache(['laboratories']);
            break;

        case 'Appointment\\Status\\Reason':
            bustCache(['appointment-status-reasons']);
            break;
        
        case 'Appointment\\Status':
            bustCache(['appointment-statuses']);
            break;
        
        case 'Appointment\\DeleteReason':
            bustCache(['appointment-delete-reasons']);
            break;
        
        case 'CallRequest\\Purpose':
            bustCache(['call-request-purposes']);
            break;
        
        case 'Call\\Result':
            bustCache(['call-results']);
            break;
        
        case 'Call\\DeleteReason':
            bustCache(['call-delete-reasons']);
            break;
            
        case 'Clinic':
            bustCache(['clinics']);
            break;
        
        case 'Employee\\Position':
            bustCache(['positions']);
            break;
        
        case 'Service\\PaymentDestination':
            bustCache(['payment-destinations']);
            break;
        
        case 'Specialization':
            bustCache(['specializations']);
            break;
        
        case 'Handbook':
            bustCache(['handbook']).then(() => {
                handbook.load(true);
            });
            break;
    }
});