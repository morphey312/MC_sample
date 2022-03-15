import DigitalSign from '@/components/general/digital-sign/DigitalSign.vue';

export default {
    methods: {
        signData(data, signed, {cancelled, title, consent, checkbox} = {}) {
            this.$confirmWhen(!!consent || !!checkbox, consent, () => {
                this.$modalComponent(DigitalSign, {
                    data,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                        (cancelled || (() => {}))();
                    },
                    signed: (dialog, data) => {
                        dialog.close();
                        signed(data);
                    },
                }, {
                    header: title || __('Цифровая подпись'),
                    width: '300px',
                });
            }, {
                title: __('Принятие соглашений'),
                confirmBtnText: __('Далее'),
                ...(checkbox ? {doubleConfirmMessage: checkbox} : {}),
            });
        },
    }
};