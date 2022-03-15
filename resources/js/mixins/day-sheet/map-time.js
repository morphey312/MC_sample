import Appointment from '@/models/appointment';
import BaseRepository from '@/repositories/base-repository';

export default {
	methods: {
        initMomentValues(list, date) {
            return _.map(list, (item) => {
                    if(_.isNil(item.momented)){
                        item.momented = {
                            start: this.$moment(`${date} ${item.start}`),
                            end: this.$moment(`${date} ${item.end}`),
                        }
                    }
                    item.start = item.start.substring(0, 5);
                    item.end = item.end.substring(0, 5);

                    return item;
                });
        },
        getAppointmentDuration(appointment) {
            return this.$moment(appointment.date + ' ' + appointment.end).diff(this.$moment(appointment.date + ' ' + appointment.start), "minutes");
        },
        mapAppointmentTimes(list) {
            _.each(list, (day) => {
               _.each(day.appointments, (appointment) => {
                    this.setMomentedValues(appointment);
               })  
            });

            return list;
        },
        setMomentedValues(appointment) {
            let start = this.getMomentedTime(appointment);
            let end = this.getMomentedTime(appointment, 'end');
            let momented = {
                start: start,
                end: end,
                scheduleStart: start.format("HH:mm"),
                scheduleEnd: end.format("HH:mm")
            }
            
            if(_.isFunction(appointment.set)) {
                appointment.set('momented', momented);
            } else {
                appointment.momented = momented;
            }
        },
        getMomentedTime(appointment, field = 'start') {
            return this.$moment(appointment.date + ' ' + appointment[field])
        },
        sortByTimeStart(data) {
            if(_.isEmpty(data)){
                return [];
            }

            return _.orderBy(data, (item) => {
                return this.getMomentedTime(item);
            });
        },
        mapAppointmentsData(day) {
            let appointments = this.sortByTimeStart(day.appointments);
            let model = new Appointment();

            _.each(appointments, (appointment) => {
                this.setMomentedValues(appointment);
                
                if( !(appointment instanceof Appointment) ) {
                    appointment = model.castToInstance(Appointment, appointment);
                }
                
                if(_.isFunction(this.countCells)) {
                    this.countCells(appointment);
                    this.appointmentList[appointment.momented.scheduleStart] = appointment;
                }
            });
        },
	}
}