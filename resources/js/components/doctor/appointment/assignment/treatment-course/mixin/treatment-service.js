export default {
    methods: {
        isTreatmentService(service) {
            return _.isFilled(service.treatment_assignment_id) ||
                   (_.isVoid(service.treatment_assignment_id) && _.isVoid(service.card_assignment_id));
        },
    },
}