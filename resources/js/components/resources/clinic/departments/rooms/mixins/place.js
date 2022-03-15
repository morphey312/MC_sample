export default {
    methods: {
        placeCreated(place) {
            let numberIndex = this.model.places.findIndex(item => item.number === place.number);
            if (numberIndex !== -1) {
                return this.$error(__('Такой номер в плате уже существует'));
            }
            this.model.places.push(place);
        },
        placeUpdated({place, index}) {
            let placeIndex = index;
            if (!place.isNew() || index === -1) {
                placeIndex = this.model.places.findIndex(item => {
                    return item.id === place.id
                });
            }
            this.model.places.splice(placeIndex, 1, place);
        },
    },
}