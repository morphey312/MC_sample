<template>
    <div>
        <template v-if="shouldAuthorize">
            <p>{{ __('Авторизируйтесь в сервисе, чтобы получить список ваших ключей.') }}</p>
            <form-input
                :entity="authorization"
                property="login"
                :label="__('Логин')" />
            <form-input
                type="password"
                :entity="authorization"
                property="password"
                :label="__('Пароль')" />
        </template>
        <template v-else>
            <form-select 
                :entity="key"
                property="name"
                :label="__('Выберите ключ')"
                :options="keys">
                <a 
                    slot="label-addon"
                    href="#"
                    @click.prevent="refresh">
                    {{ __('Обновить список') }}
                </a>
            </form-select>
            <form-input
                type="password"
                :entity="key"
                property="password"
                :label="__('Пароль к ключу')" />
        </template>
        <div
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button 
                v-if="shouldAuthorize"
                :disabled="!canLogin"
                type="primary"
                @click="authorize">
                {{ __('Далее') }}
            </el-button>
            <el-button 
                v-else
                type="primary"
                :disabled="!canSign"
                @click="sign">
                {{ __('Подписать') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import depositSign from '@/services/digital-sign/depositsign';

export default {
    props: {
        data: String,
    },
    data() {
        return {
            authorization: {
                login: null,
                password: null,
            },
            key: {
                name: null,
                password: null,
            },
            shouldAuthorize: false,
            keys: [],
            loading: false,
        };
    },
    computed: {
        canLogin() {
            return this.loading === false
                && _.isFilled(this.authorization.login)
                && _.isFilled(this.authorization.password);
        },
        canSign() {
            return this.loading === false
                && _.isFilled(this.key.name)
                && _.isFilled(this.key.password);
        },
    },
    mounted() {
        if (depositSign.isAuthorized()) {
            this.getKeys();
        } else {
            this.shouldAuthorize = true;
        }
    },
    methods: {
        getKeys() {
            this.keys = depositSign.getKeys().map((key => ({
                id: key.name,
                value: key.label,
                label: key.description,
            })));
            if (this.keys.length === 1) {
                this.key.name = this.keys[0].id;
            }
            this.shouldAuthorize = false;
        },
        authorize() {
            this.loading = true;
            this.$clearErrors();
            depositSign
                .authorize(this.authorization.login, this.authorization.password)
                .then(() => {
                    this.loading = false;
                    this.getKeys();
                })
                .catch((e) => {
                    this.loading = false;
                    if (e.message === 'Unauthorized') {
                        this.$displayErrors({
                            errors: {
                                password: [__('Неправильный логин или пароль')],
                            },
                        });
                    } else {
                        this.$error(__('Не удалось авторизоваться в хранилище ключей.'));
                    }
                });
        },
        cancel() {
            this.$emit('cancel');
        },
        sign() {
            this.loading = true;
            this.$clearErrors();
            depositSign
                .sign(this.data, this.key.name, this.key.password)
                .then((data) => {
                    this.loading = false;
                    this.$emit('signed', data);
                })
                .catch((e) => {
                    this.loading = false;
                    this.$displayErrors({
                        errors: {
                            password: [__('Не удалось считать ключ, проверьте правильность пароля')],
                        },
                    });
                });
        },
        refresh() {
            this.shouldAuthorize = true;
            depositSign.cleanup();
        }
    }
}
</script>