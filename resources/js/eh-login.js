import lts from '@/services/lts';

lts.userData = {
    id: window.login.user.id,
    login: window.login.user.login,
    employee: window.login.user.employee,
};

lts.token = window.login.token;
lts.ehealth_token = window.login.ehealth_token;

location.href = '/';