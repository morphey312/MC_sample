<template>
</template>

<script>
export default {
    data() {
        return {
            activeItem: {},
            showCreateModal: false,
            showEditModal: false,
            showDeleteModal: false,
            editActive: true,
        }
    },
    mounted() {
        this.$watch('activeItem', (val) => {
            this.editActive = _.isEmpty(val);
        });
    },
    methods: {
        setActiveItem(item) {
            this.activeItem = _.isNil(item) ? {} : item;
        },
        toogleCreateModal() {
            this.showCreateModal = !this.showCreateModal;
        },
        toogleEditModal() {
            this.showEditModal = !this.showEditModal;
        },
        toogleDeleteModal() {
            this.showDeleteModal = !this.showDeleteModal;
        },
        created(edited) {
            this.toogleCreateModal();
            this.refresh(edited);
        },
        updated(edited) {
            this.toogleEditModal();
            this.refresh(edited);
        },
        deleted(edited) {
            this.toogleDeleteModal();
            this.refresh(edited, true);
        },
        refresh(edited, dissmiss = false) {
            if (edited.updated === true) {
                this.$refs.table.refresh(dissmiss);
            }
        }
    }
}
</script>