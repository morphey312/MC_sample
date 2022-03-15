<template>
    <el-container
        :key="activeLang"
        v-loading.lock.fullscreen="loading"
    >
        <template v-if="!loading">
            <side-bar
                ref="sidebar"
                @menuCollapsed="setMenuState"
            />
            <el-main>
                <call-alert/>
                <complete-processing-alert/>
                <ehealth-alert/>
                <keep-alive
                    include="Analyses,Services"
                    max="1"
                >

                    <start-screen v-if="$route.name === 'main'"/>
                    <router-view></router-view>
                </keep-alive>
                <memory/>
                <div class="mob-menu-toggle " :class="menuTogglerClass">
                    <a
                        href="#"
                        class="toggle-menu-btn "
                        :class="arrowClass"
                        @click.prevent="toggleMenu"
                    ></a>
                </div>
            </el-main>
        </template>
    </el-container>
</template>

<script>
import SideBar from '@/components/general/SideBar.vue';
import CallAlert from '@/components/call-center/voip/CallAlert.vue';
import EhealthAlert from '@/components/ehealth/Alert.vue';
import ActEditForm from '@/components/agreement-of-prices/FormEdit.vue';
import CompleteProcessingAlert from '@/components/call-center/voip/CompleteProcessingAlert.vue';
import Memory from '@/components/general/Memory.vue';
import CancelProcessing from '@/components/call-center/wait-list-records/CancelProcessing.vue';
import handbook from '@/services/handbook';
import permissions from '@/services/permissions';
import Act from '@/models/price-agreement-act';
import broadcast from '@/services/broadcast';
import {UA} from '@/services/sip-ua';
import ShiftManager from '@/services/cashier/shift-manager';
import tabsync from '@/services/tabsync';
import logger from '@/services/logging';
import lts from '@/services/lts';
import translationServer from '@/services/translation';
import localizeComponents from '@/lang/languages';
import auth from '@/services/auth';
import {
    bustCache,
    bustLocalizedCache,
} from '@/services/service-worker';
import {
    CHANNEL_GENERAL,
    CHANNEL_OPERATORS,
    CHANNEL_USER,
} from '@/services/broadcast';
import {STATE_ONLINE} from '@/services/sip-ua/state-manager';
import CONSTANTS from '@/constants';
import StartScreen from './StartScreen.vue';

export default {
    components: {
        StartScreen,
        SideBar,
        CallAlert,
        CompleteProcessingAlert,
        EhealthAlert,
        Memory,
    },
    data() {
        return {
            loading: true,
            activeLang: translationServer.lang,
            isMenuCollapsed: false,
        }
    },
    computed: {
        arrowClass() {
            return this.isMenuCollapsed ? 'el-icon-arrow-right' : 'el-icon-arrow-left';
        },
        menuTogglerClass() {
            if(this.isIos()) {
                return this.isMenuCollapsed ? 'open-menu' : 'close-menu';
            }
        }
    },
    created() {
        this.destroyed = false;
        if (!lts.language) {
            translationServer.lang = this.$store.state.user.employee.preferred_language;
        }

        translationServer.changed((lang) => {
            localizeComponents(lang);
            lts.language = lang;
            this.loading = true;
            bustLocalizedCache().then(() => {
                return handbook.load(true);
            }).then(() => {
                this.loading = false;
                this.activeLang = lang;
            });
        });
    },
    mounted() {
        let user = this.$store.state.user;

        if (window.appConfig.env === 'prod') {
            logger.enable({
                id: user.employee_id,
                name: user.full_name,
            });
        }

        if (user.hasVoIP) {
            let head = document.getElementsByTagName('head')[0];

            let script = document.createElement('script');
            script.src = '/vendor/sipml5/SIPml-api-no-ice.js';
            script.type = 'text/javascript';

            head.appendChild(script)
        }

        this.load().then(() => {
            this.loading = false;
            // Init broadcast listener
            broadcast.subscribe((event, data) => {
                this.$eventHub.$emit(`broadcast.${event}`, data);
            }, (channel) => {
                switch (channel) {
                    case CHANNEL_GENERAL:
                        this.$error(__('Не удалось подписаться на основной канал оповещений'));
                        break;
                    case CHANNEL_OPERATORS:
                        this.$error(__('Не удалось подписаться на канал оповещений операторов'));
                        break;
                    case CHANNEL_USER:
                        this.$error(__('Не удалось подписаться на персональный канал оповещений'));
                        break;
                }
            });
            _.waitUntil(() => broadcast.isConnected, 1000, 30000).catch(() => {
                this.$error(__('Не удалось подключиться к сервису оповещений'));
            }).then(() => {
                // Init SIP user agent
                _.waitUntil(() => tabsync.isPrimaryTab !== undefined).then(() => {
                    if (tabsync.isPrimaryTab) {
                        UA.init().then(() => {
                            UA.connect();
                        });
                    }
                });
            });
        });
        this.$eventHub.$on('logout', () => {
            this.destroy();
            this.loading = true;
        });
        this.$eventHub.$on('parkedcall:gone', (call) => {
            this.$warning(__('Удерживаемый абонент {name} ({number}) покинул линию.', {
                name: call.name || __('Неизвестный'),
                number: call.number
            }));
        });
        this.$eventHub.$on('broadcast.wait_list_record_processed', (waitListRecord) => {
            this.verifyRecordProcessing(waitListRecord);
        });
        this.$eventHub.$on('broadcast.gitlab_pipeline_finished', () => {
            this.$info(__('В MedCenter+ произведено обновление. Рекомендуем выполнить комбинацию клавиш Ctrl+F5,' +
                ' а также очистить кеш для корректной работы в системе'), {duration: 0});
        });
        this.$eventHub.$on('show_price_agrement_prices', (actId) => {
            this.showPriceAgreement(actId);
        });
    },
    beforeDestroy() {
        this.destroy();
    },
    methods: {
        load() {
            return auth.refreshIfOlder(1000).then(() => {
                return Promise.all([
                    handbook.load(),
                    permissions.load(),
                ]);
            });
        },
        destroy() {
            if (!this.destroyed) {
                broadcast.unsubscribe();
                broadcast.disconnect();
                UA.endSession();
                UA.destroy();
                permissions.flush();
                ShiftManager.destroy();
                this.destroyed = true;
            }
        },
        setMenuState(val) {
            this.isMenuCollapsed = val;
        },
        toggleMenu() {
            if (this.$refs.sidebar) {
                this.$refs.sidebar.toggle();
            }
        },
        isIos() {
            return navigator.platform.toLowerCase().indexOf('ipad') !== -1;
        },
        verifyRecordProcessing(waitListRecord) {
            if (!waitListRecord || !waitListRecord.data) {
                return;
            }

            let processState = (this.$store.state && this.$store.state.processState) ? this.$store.state.processState : null;

            if (processState
                && processState.processLog
                && (processState.processLog.wait_list_record === waitListRecord.data.id)
                && (waitListRecord.data.status === CONSTANTS.WAIT_LIST_RECORD.STATUS.PROCESSED)
            ) {
                this.$modalComponent(CancelProcessing, {}, {
                    cancel: (dialog) => {
                        dialog.close();
                        processState.reset();
                        this.$store.commit('processState', processState);
                        UA.stateManager.transit(STATE_ONLINE);
                    },
                }, {
                    width: '400px',
                    closeOnEscape: false,
                    showClose: false,
                });
            }
        },
        showPriceAgreement(actId) {
            let act = new Act({id: actId});
            act.fetch().then(() => {
                this.$modalComponent(ActEditForm, {
                    item: act,
                }, {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                    }, {
                        width: '1200px',
                    });
                });
        }
    },
}
</script>
