import WarnExtChanges from '@/mixins/warn-external-changes';

export default {
    mixins: [
        WarnExtChanges,
    ],
    methods: {
        cancel() {
            this.model.isCancel = false;
            this.model.isClose = false;
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();
            this.confirmExternalOverwrite(() => {
                this.model.save().then((response) => {
                    this.$emit('saved', this.model);
                }).catch((e) => {
                    this.onSaveError(e);
                    this.$displayErrors(e);
                });
            });
        },
        onSaveError() {
        },
    },
};
