export default {
    methods: {
        isMobileNumber(num) {
            return typeof num === 'string' && [
                '+38039',
                '+38067',
                '+38068',
                '+38096',
                '+38097',
                '+38098',
                '+38050',
                '+38066',
                '+38095',
                '+38099',
                '+38063',
                '+38093',
                '+38091',
                '+38092',
                '+38094',
            ].indexOf(num.substr(0, 6)) !== -1;
        },
    },
}
