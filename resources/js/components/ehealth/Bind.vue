<template>
    <page
        :title="__('Создание корневого пользователя eHealth')">
        <section>
            <el-button
                :loading="loading"
                @click="create">
                {{ __('Создать корневого пользователя') }}
            </el-button>
        </section>
    </page>
</template>

<script>
import ehealth from '@/services/ehealth';

export default {
    data() {
        return {
            loading: false,
        }
    },
    methods: {
        create() {
            this.loading = true;
            ehealth.getMisInitUrl().then((url) => {
                this.loading = false;
                if (url === false) {
                    this.$error(__('Корневой пользователь уже задан'));
                } else {
                    location.href = url;
                }
            }).catch(() => {
                this.loading = false;
                this.$error(__('Не удалось получить URL для инициализации корневого пользователя'));
            });
        }
    }
}
</script>