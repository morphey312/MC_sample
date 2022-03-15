<template>
    <el-main>
        <el-row>
            <el-col
                :span="12"
                :offset="6">
                <div class="login-form-content">
                    <h1>{{ appName }} {{ __('приветствует вас') }}</h1>
                    <form
                        v-if="ehealthLogin"
                        @submit.prevent="submitEhealth">
                        <div style="max-width: 100%">
                            <form-row
                                name="loginAs"
                                :label="__('Авторизироваться как')">
                                <el-radio-group v-model="ehealthLoginAs">
                                    <el-radio-button
                                        v-for="opt in ehealthLoginOptions"
                                        :key="opt.id"
                                        :label="opt.id">
                                        {{ opt.value }}
                                    </el-radio-button>
                                </el-radio-group>
                            </form-row>
                            <form-row
                                v-if="!isMisAdmin"
                                name="email"
                                :label="__('Email')">
                                <el-input
                                    v-model="ehealthEmail" />
                            </form-row>
                        </div>
                        <div class="form-footer text-center">
                            <el-button
                                :disabled="!canLoginEhealth"
                                :loading="loading"
                                type="primary"
                                native-type="submit">
                                {{ __('Продолжить') }}
                            </el-button>
                            <el-button
                                @click="ehealthLogin = false">
                                {{ __('Обычная авторизация') }}
                            </el-button>
                        </div>
                    </form>
                    <form
                        v-else
                        @submit.prevent="submit">
                        <div style="max-width: 100%">
                            <form-row
                                name="login"
                                :label="__('Логин')">
                                <el-input
                                    v-model="login" />
                            </form-row>
                            <form-row
                                name="password"
                                :label="__('Пароль')">
                                <el-input
                                    v-model="password"
                                    type="password" />
                            </form-row>
                        </div>
                        <div class="form-footer text-center">
                            <el-button
                                :disabled="!canLogin"
                                :loading="loading"
                                type="primary"
                                native-type="submit">
                                {{ __('Войти') }}
                            </el-button>
                            <el-button
                                @click="ehealthLogin = true">
                                {{ __('Авторизация через eHealth') }}
                            </el-button>
                        </div>
                    </form>
                </div>
            </el-col>
        </el-row>
    </el-main>
</template>

<script>
import auth from '@/services/auth';
import { bustCache } from '@/services/service-worker';
import ehealth from '@/services/ehealth';
import store from "@/store";

const OWNER = 'owner';
const MIS = 'mis';

export default {
    data() {
        return {
            login: '',
            password: '',
            loading: false,
            ehealthEmail: '',
            ehealthLogin: false,
            ehealthLoginAs: OWNER,
            ehealthLoginOptions: [
                {id: OWNER, value: __('Сотрудник')},
                {id: MIS, value: __('Администратор МИС')},
            ],
        };
    },
    computed: {
        canLogin() {
            return this.login.length !== 0
                && this.password.length !== 0;
        },
        canLoginEhealth() {
            return this.isMisAdmin || this.ehealthEmail.length !== 0 ;
        },
        appName() {
            return window.appConfig.name;
        },
        isMisAdmin() {
            return this.ehealthLoginAs === MIS;
        },
    },
    mounted() {
        bustCache({except: ['static']});
    },
    methods: {
        submit() {
            this.$clearErrors();
            this.loading = true;
            auth.login(this.login, this.password)
                .then((response) => {
                    this.$eventHub.$emit('login');
                    this.loading = false;
                    if (store.state.entryRoute){
                        let route = store.state.entryRoute;
                        store.commit('clearEntryRoute');
                        this.$router.push({name: route.name, query: route.query, params: route.params});
                    } else {
                        this.$router.push({name: 'main'});
                    }
                }).catch((e) => {
                    this.loading = false;
                    this.$displayErrors(e);
                });
        },
        submitEhealth() {
            if (this.isMisAdmin) {
                location.href = ehealth.getMisLoginUrl();
            } else {
                this.$clearErrors();
                this.loading = true;
                ehealth.getMspLoginUrl(this.ehealthEmail).then((url) => {
                    this.loading = false;
                    location.href = url;
                }).catch((e) => {
                    this.loading = false;
                    this.$displayErrors(e);
                });
            }
        },
    }
}
</script>
