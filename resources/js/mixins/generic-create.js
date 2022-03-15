export default {
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
        onSaveError() {
        },
    },
};