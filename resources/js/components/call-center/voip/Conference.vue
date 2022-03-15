<template>
    <div v-loading="loading" class="sections-wrapper">
        <section>
            <div class="paragraph">
                <h4>{{ __('На удержании') }}</h4>
                <table class="vuetable table">
                    <thead>
                        <tr>
                           <th width="40%">{{ __('Абонент') }}</th>
                           <th width="35%">{{ __('Номер телефона') }}</th>
                           <th width="25%"></th>
                        </tr>
                    </thead>
                    <tbody v-if="ua.parkedCalls.length !== 0">
                        <tr v-for="parked in ua.parkedCalls">
                            <td>{{ parked.name }}</td>
                            <td>{{ parked.number }}</td>
                            <td>
                                <a
                                    href="#"
                                    @click.prevent="join(parked)">
                                    {{ __('Присоединить') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="3" class="vuetable-empty-result">{{ __('Нет абонентов на удержании') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="paragraph">
                <h4>{{ __('Сейчас на связи') }}</h4>
                <table class="vuetable table">
                    <thead>
                        <tr>
                           <th width="40%">{{ __('Абонент') }}</th>
                           <th width="35%">{{ __('Номер телефона') }}</th>
                           <th width="25%"></th>
                        </tr>
                    </thead>
                    <tbody v-if="ua.call !== null && ua.call.participants.length !== 0">
                        <tr v-for="member in ua.call.participants">
                            <td>{{ member.versaName }}</td>
                            <td>{{ member.phoneNumber }}</td>
                            <td>
                                <a 
                                    href="#"
                                    @click.prevent="kick(member)">
                                    {{ __('Отключить') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="3" class="vuetable-empty-result">{{ __('Нет абонентов на связи') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <div class="dialog-footer text-right">
            <el-button
                @click="leave">
                {{ __('Покинуть конференцию') }}
            </el-button>
            <el-button
                type="primary"
                @click="close">
                {{ __('Скрыть') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ConferenceCall from '@/services/sip-ua/conference-call';

export default {
    props: {
        ua: Object,
    },
    data() {
        return {
            loading: false,
        };
    },
    methods: {
        close() {
            this.$emit('close');
        },
        join(parked) {
            this.loading = true;
            this.ua.joinConference(parked)
                .catch(() => {
                    this.$error(__('Не удалось подключить абонента к конференции'));
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        kick(member) {
            this.$confirm(__('Вы уверены, что хотите отключить этого абонента?'), () => {
                this.loading = true;
                this.ua.leaveConference(member.phoneNumber)
                    .catch(() => {
                        this.$error(__('Не удалось отключить абонента от конференции'));
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            });
        },
        leave() {
            this.$confirm(__('Вы уверены, что хотите покинуть эту конференцию?'), () => {
                this.loading = true;
                this.ua.terminate();
            });
        },
    },
    watch: {
        ['ua.call'](val) {
            if (val === null) {
                this.close();
            }
        },
    },
};
</script>