import FilterState from '@/mixins/filter-state';
import PatientUser from "../models/patient/user";

export default {
    mixins: [
        FilterState,
    ],
    props: {
        modalComponent: Object,
    },
    data() {
        let defaultFilters = this.getDefaultFilters();
        let filters = this.getStoredFilter(defaultFilters);
        return {
            filters,
            displayFilter: !_.isEqual(filters, defaultFilters),
            activeItem: null,
            lastActiveItemId: null,
        };
    },
    methods: {
        getLoggedUserClinics() {
            return this.$store.state.user.clinics;
        },
        changeFilters(filters) {
            this.storeFilter(filters);
            this.filters = filters;
        },
        clearFilters() {
            this.forgetFilter();
            this.filters = this.getDefaultFilters();
        },
        getDefaultFilters() {
            return {};
        },
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        },
        syncFilters(updates) {
            this.getFilter().sync(updates);
        },
        create() {
            let opts = {
                ...this.defaultModalOptions(),
                ...this.getModalOptions(),
            };
            let messages = {
                ...this.defaultMessages(),
                ...this.getMessages(),
            };
            (opts.modal ? opts.modal.pushComponent : this.$modalComponent)(
                opts.createForm,
                _.isFunction(opts.createProps) ? opts.createProps() : opts.createProps,
                {
                    cancel: (dialog) => {
                        if (opts.modal) {
                            opts.modal.popComponent();
                        } else {
                            dialog.close();
                        }
                    },
                    created: (dialog, model) => {
                        if (opts.modal) {
                            opts.modal.popComponent();
                        } else {
                            dialog.close();
                        }
                        if (messages.created) {
                            this.$info(messages.created);
                        }
                        this.onCreated(model);
                        this.lastActiveItemId = model.id;
                        this.refresh();
                    },
                    ...opts.events,
                },
                {
                    header: opts.createHeader,
                    width: opts.width,
                    backText: opts.backText,
                    beforeClose: opts.beforeClose,
                    onClosed: opts.onClosed,
                    customClass: opts.createCustomClass,
                }
            );
        },
        edit() {
            let opts = {
                ...this.defaultModalOptions(),
                ...this.getModalOptions(),
            };
            let messages = {
                ...this.defaultMessages(),
                ...this.getMessages(),
            };
            (opts.modal ? opts.modal.pushComponent : this.$modalComponent)(
                opts.editForm,
                {
                    item: this.activeItem,
                    ...(_.isFunction(opts.editProps) ? opts.editProps() : opts.editProps),
                },
                {
                    cancel: (dialog) => {
                        if (opts.modal) {
                            opts.modal.popComponent();
                        } else {
                            dialog.close();
                        }
                    },
                    saved: (dialog, model) => {
                        if (opts.modal) {
                            opts.modal.popComponent();
                        } else {
                            dialog.close();
                        }
                        if (messages.updated) {
                            this.$info(messages.updated);
                        }
                        this.onUpdated(model);
                        this.lastActiveItemId = model.id;
                        this.refresh();
                    },
                    ...opts.events,
                },
                {
                    header: opts.editHeader,
                    width: opts.width,
                    backText: opts.backText,
                    beforeClose: opts.beforeClose,
                    onClosed: opts.onClosed,
                    customClass: opts.editCustomClass,
                }
            );
        },
        remove() {
            let messages = {
                ...this.defaultMessages(),
                ...this.getMessages(),
            };
            let model = this.activeItem;
            let attributes = { ...this.activeItem._attributes };
            this.$confirm(messages.deleteConfirmation, () => {
                this.performDelete(model).then(() => {
                    if (messages.deleted) {
                        this.$info(messages.deleted);
                    }
                    this.onDeleted(attributes);
                    this.lastActiveItemId = null;
                    this.activeItem = null;
                    this.refresh();
                });
            });
        },
        performDelete(model) {
            return model.delete();
        },
        onCreated(model) {
        },
        onUpdated(model) {
        },
        onDeleted(attributes) {
        },
        refresh() {
            this.getManageTable().refresh();
        },
        refreshed() {
            if (this.lastActiveItemId !== null) {
                this.getManageTable().updateSelection((item) => item.id == this.lastActiveItemId);
            }
        },
        getManageTable() {
            return this.$refs.table.$refs.table;
        },
        getFilter() {
            return this.$refs.filter;
        },
        getModalOptions() {
            return {};
        },
        defaultModalOptions() {
            return {
                createForm: null,
                createProps: {},
                editForm: null,
                editProps: {},
                createHeader: __('Добавить'),
                editHeader: __('Редактировать'),
                editCustomClass: "",
                createCustomClass: "",
                width: '600px',
                backText: __('Назад'),
                modal: null,
                beforeClose: undefined,
                onClosed: undefined,
                events: {},
            };
        },
        getMessages() {
            return {};
        },
        defaultMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту запись?'),
                deleted: __('Запись была успешно удалена'),
                created: __('Запись была успешно добавлена'),
                updated: __('Запись была успешно обновлена'),
            };
        },
        passwordReset() {
            this.$confirm(__('Вы уверены что хотите сбросить пароль?'), () => {
                let model = this.activeItem;
                let attributes = { ...this.activeItem._attributes };

                return model.passwordReset({id: attributes.id}).then(() => {
                    this.$info(__('Пароль был успешно сброшен'));
                });
            });
        },
    },
};
