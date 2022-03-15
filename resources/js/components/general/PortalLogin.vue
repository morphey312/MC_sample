<template>
    <div class="portal-login">
        <p v-if="error">
            <svg-icon name="warning" class="icon-small mr-10" />
            {{ __('Не удалось авторизироваться на обучающем портале.') }}
        </p>
        <p v-else-if="link === null">
            {{ __('Авторизация на обучающем портале...') }}
        </p>
        <p v-else>
            {{ __('Вы успешно авторизировались на обучающем портале. Нажмите "Войти", чтобы продолжить') }}
        </p>
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="link === null"
                @click.prevent="go" >
                {{ __('Войти') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import auth from '@/services/auth';

export default {
    data() {
        return {
            error: false,
            link: null,
        };
    },
    mounted() {
        auth.hrPortalLogin().then((link) => {
            this.link = link;
        }).catch(() => {
            this.error = true;
        });
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        go() {
            window.open(this.link, '_blank');
            this.$emit('close');
        },
    }
}
</script>