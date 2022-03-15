<template>
    <div style="display: flex;justify-content: center">
        <div class="empty-content-wrapper with-circle">
            <div class="empty-content">
                <div>
                    <div class="empty-content-img"></div>
                    <span>
                        <a
                            href="#"
                            @click.prevent="sessionStart"
                            :disabled="!isCashier">
                            {{ __('Откройте смену') }}
                        </a><br> {{ __('и добавьте первый платеж') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="empty-content-wrapper with-circle">
            <div class="empty-content">
                <div>
                    <div class="empty-content-img"></div>
                    <span>
                        <a
                            href="#"
                            @click.prevent="checkboxSessionStart"
                            :disabled="!isCashier">
                            {{ __('Откройте смену в Checkbox') }}
                        </a><br> {{ __('и добавьте первый платеж') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        isCashier: Boolean,
    },
    methods: {
        sessionStart() {
            if (!this.isCashier) {
                return false;
            }
            this.$emit('session-start');
        },
        checkboxSessionStart() {
            if (!this.isCashier) {
                return false;
            }
            this.$emit('checkbox-session-start');
        },
    },
}
</script>