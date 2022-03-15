import AppointmentService from '@/models/appointment/service';

export default {
    methods: {
        updateServiceDebt(service_id, service_name, onUpdate = null) {
            this.$confirm(__('Вы уверены что пациент не является должником по услуге') + ' ' + service_name + '?', () => {
                let service = new AppointmentService({id: service_id});
                service.updateServiceDebt({not_debt: true}).then(() => {
                    this.$info(__('Пациент не является должником'));
                    if (onUpdate) {
                        onUpdate();
                    }
                });
            });
        },
        updateServiceSetDebt(service_id, service_name, onUpdate = null) {
            this.$confirm(__('Вы уверены что пациент является должником по услуге') + ' ' + service_name + '?', () => {
                let service = new AppointmentService({id: service_id});
                service.updateServiceDebt({not_debt: false}).then(() => {
                    this.$info(__('Пациент стал должником'));
                    if (onUpdate) {
                        onUpdate();
                    }
                });
            });
        },
    }
}
