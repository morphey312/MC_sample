<template>
    <modal
        v-bind="currentModalOptions"
        :visible="visible"
        @close="close"
        @closed="closed">
        <template slot="header-addon">
            <template v-for="(item, index) in stack">
                <component
                    v-if="item.modalOptions.headerAddon !== undefined"
                    v-show="index === stack.length - 1"
                    ref="headerAddon"
                    :key="index"
                    :is="item.modalOptions.headerAddon.component"
                    v-bind="item.modalOptions.headerAddon.props" />
                <span 
                    v-else
                    ref="headerAddon"
                    :key="index"
                    style="display: none" />
            </template>
        </template>
        <template v-for="(item, index) in stack">
            <section 
                v-if="index !== 0"
                :key="index"
                class="back">
                <a 
                    href="#"
                    @click.prevent="popComponent">
                    <svg-icon name="arrow-back" class="icon-blue" />
                    {{ item.modalOptions.backText || __('Назад') }}
                </a>
            </section>
            <component
                v-show="index === stack.length - 1"
                ref="content"
                :key="index"
                :is="item.component"
                v-bind="item.componentProps" />
        </template>
    </modal>
</template>

<script>
import Modal from '@/components/general/Modal.vue';

export default {
    components: {
        Modal
    },
    props: {
        component: {
            type: [Object, String],
            required: true,
        },
        componentProps: {
            type: Object,
            default: () => ({}),
        },
        modalOptions: {
            type: Object,
            required: true,
        },
        eventListeners: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            visible: false,
            stack: [],
        };
    },
    computed: {
        currentModalOptions() {
            return this.stack.length === 0 ? this.modalOptions : this.stack[this.stack.length - 1].modalOptions;
        },
    },
    mounted() {
        document.body.appendChild(this.$el);
        this.stack.push({
            component: this.component,
            componentProps: {...this.componentProps, modalComponent: this},
            modalOptions: this.modalOptions,
            eventListeners: this.eventListeners,
        });
        this.visible = true;
        this.$nextTick(() => {
            this.initTopComponent();
            this.initTopHeaderAddon();
        });
    },
    beforeDestroy() {
        this.cleanupRouterApps();
    },
    methods: {
        pushComponent(component, props = {}, eventListeners = {}, modalOptions = {}) {
            this.stack.push({
                component: component,
                componentProps: {...props, modalComponent: this},
                modalOptions: {...this.modalOptions, ...modalOptions},
                eventListeners: eventListeners,
            });
            this.$nextTick(() => {
                this.initTopComponent();
                this.initTopHeaderAddon();
            });
        },
        popComponent() {
            let modalOptions = this.currentModalOptions;
            if (modalOptions.beforeClose === undefined || 
                modalOptions.beforeClose(this) !== false) {
                this.stack.pop();
                if (modalOptions.onClosed !== undefined) {
                    modalOptions.onClosed(this);
                }
            }
        },
        close() {
            if (this.modalOptions.beforeClose === undefined || 
                this.modalOptions.beforeClose(this) !== false) {
                this.visible = false;
            }
        },
        closed() {
            if (this.modalOptions.onClosed !== undefined) {
                this.modalOptions.onClosed(this);
            }
            document.body.removeChild(this.$el);
            this.$destroy();
        },
        getTopRef(ref) {
            return _.isArray(ref) ? ref[ref.length - 1] : ref;
        },
        getTopComponent() {
            return this.getTopRef(this.$refs.content);
        },
        getTopHeaderAddon() {
            return this.getTopRef(this.$refs.headerAddon);
        },
        initTopComponent() {
            let component = this.getTopComponent();
            let events = this.stack[this.stack.length - 1].eventListeners;
            this.mapEvents(component, events);
        },
        initTopHeaderAddon() {
            let addon = this.getTopHeaderAddon();
            let addonData = this.stack[this.stack.length - 1].modalOptions.headerAddon;
            if(addonData !== undefined && addonData.eventListeners !== undefined) {
                this.mapEvents(addon, addonData.eventListeners);
            }
        },
        mapEvents(source, map) {
            Object.keys(map).forEach((key) => {
                source.$off(key);
                source.$on(key, (data) => {
                    map[key](this, data, source);
                });
            });
        },
        cleanupRouterApps() {
            let apps = this.$router.apps;
            let selfIndex = apps.indexOf(this);
            if (selfIndex !== -1) {
                apps.splice(selfIndex, 1);
            }
        },
    },
};
</script>