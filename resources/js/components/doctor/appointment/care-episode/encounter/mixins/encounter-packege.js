import Encounter from '@/models/ehealth/encounter';

export default {
    date() {
        return {
            model: {},
        }
    },
    methods: {
        getEncounterData($scope) {
            this.model = new Encounter({id: this.encounter.id})
            return this.model.fetch([$scope]).then(() => {
                return this.model
            });
        },
        encounterPackegeUpdateData(model, data) {
            return data.map((e) => {
                if (e.data.id === model.id) {
                    return {
                        id: model.id,
                        data: model,
                    };
                }

                return e;
            });
        },
        encounterPackegeCreateData(model, data) {
            return [
                ...data,
                {
                    id: model.id,
                    data: model,
                },
            ];
        },
        encounterPackegeDeleteData(model, data) {
            return data.filter((e) => e.id !== id)
        },
    },
}
