import BaseEvent from './base-event';

class AppointmentEvent extends BaseEvent
{
    /**
     * Constructor
     */ 
    constructor(appointment) {
        super(appointment);
        this._start = new Date(appointment.date + ' ' + appointment.start);
        this._end = new Date(appointment.date + ' ' + appointment.end);
        this._title = appointment.patient.lastname + ' ' + appointment.patient.firstname;
    }
}

export default AppointmentEvent;