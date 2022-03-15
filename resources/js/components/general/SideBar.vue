<template>
    <el-aside
        :width="sideWidth"
        :class="{ 'collapsed-aside': isCollapse }">
        <div class="aside-wrapper">
            <div class="logo">
                <i class="menu-item-icon logo-item"></i>
                <span>{{ appName }}</span>
            </div>
            <div class="side-menu-container">
                <side-menu :is-collapse="isCollapse"></side-menu>
            </div>
            <div class="menu-footer">
                <div class="user-info">
                    <svg-icon name="user-alt" class="icon-white" />
                    <span class="name">{{ userName }}</span>
                    <el-dropdown
                        placement="top-start"
                        @command="handleCommand">
                        <span class="el-dropdown-link">
                            <svg-icon
                                slot="reference"
                                name="cog-alt"
                                :title="__('Опции')"
                                class="icon-white"
                                v-bind:class="{ 'unread-notification': ($can('notification.access') && hasUnreadNotification) }"/>
                        </span>
                        
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                v-if="canSelectLanguage('ru')"
                                command="setLangRu">
                                {{ __('Язык интерфейса: Русский') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="canSelectLanguage('ua')"
                                command="setLangUa">
                                {{ __('Мова інтерфейсу: Українська') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="canSelectLanguage('sk')"
                                command="setLangSk">
                                {{ __('Jazyk rozhrania: Slovenský') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$can('notification.access')"
                                divided
                                v-bind:class="{ 'has-notification': hasUnreadNotification }"
                                 command="showNotifications">
                                {{ __('Оповещения для сотрудников') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                divided
                                command="bustCache">
                                {{ __('Очистить кэш приложения') }}
                            </el-dropdown-item>
                            
                            <el-dropdown-item
                                v-if="canSendFeedback"
                                divided
                                command="sendFeedback">
                                {{ __('Сообщить о проблеме') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                command="showJournal">
                                {{ __('Журнал сообщений') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="hasHrPortalAccount"
                                command="portalLogin">
                                {{ __('Обучающий портал') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
                <a href="#" :class="arrowClass" @click.prevent="toggle"></a>
            </div>
        </div>
    </el-aside>
</template>

<script>
import SideMenu from './menu/SideMenu.vue';
import Feedback from './Feedback.vue';
import Notifications from './Notification.vue';
import Journal from './Journal.vue';
import lts from '@/services/lts';
import { bustCache } from '@/services/service-worker';
import translationServer from '@/services/translation';
import PortalLogin from './PortalLogin.vue';

const FORCE_COLLAPSE = [
    'doctor-appointment',
    'patient-cabinet-info',
];

export default {
    components: {
        SideMenu,
    },
    data() {
        return {
            isCollapse: lts.sideMenuCollapsed === true,
        };
    },
    computed: {
        arrowClass() {
            return this.isCollapse ?  'toggle-menu-btn el-icon-arrow-right' : 'toggle-menu-btn el-icon-arrow-left';
        },
        sideWidth() {
            return this.isCollapse ? '40px' : '280px';
        },
        userName() {
            return this.$store.state.user.full_name;
        },
        hasUnreadNotification() {
            return this.$store.state.user.hasUnreadNotification;
        },
        hasHrPortalAccount() {
            return this.$store.state.user.hasHrPortalAccount;
        },
        appName() {
            return window.appConfig.name;
        },
        canSendFeedback() {
            return true;
            return this.$store.state.user.hasSentry;
        },
    },
    mounted() {
        this.checkRoute(this.$route.name);
        this.emitMenuState();
    },
    methods: {
        toggle() {
            this.isCollapse = !this.isCollapse;
            lts.sideMenuCollapsed = this.isCollapse;
            this.emitMenuState();
        },
        checkRoute(name) {
            if (FORCE_COLLAPSE.indexOf(name) !== -1 ) {
                this.isCollapse = true;
            }
            this.emitMenuState();
        },
        sendFeedback() {
            this.$modalComponent(Feedback, {}, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Обратная связь'),
                width: '400px',
            });
        },
        showNotifications() {
            this.$modalComponent(Notifications, {}, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Уведомления'),
                width: '1100px',
            });
        },
        showJournal() {
            this.$modalComponent(Journal, {}, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Журнал сообщений'),
                width: '600px',
                customClass: 'no-footer',
            });
        },
        bustCache() {
            this.$confirm(__('Вы действительно хотите удалить данные из кэша приложения? Это может временно замедлить скорость его работы.'), () => {
                bustCache().then(() => {
                    location.reload();
                });
            });
        },
        setLangRu() {
            this.setLang('ru');
        },
        setLangUa() {
            this.setLang('ua');
        },
        setLangSk() {
            this.setLang('sk');
        },
        setLang(lang) {
            translationServer.lang = lang;
        },
        handleCommand(command) {
            this[command]();
        },
        emitMenuState() {
            this.$emit('menuCollapsed', this.isCollapse);
        },
        canSelectLanguage(lang) {
            return this.$store.state.user.employee.preferred_language === lang || this.$can('general.language-selection');
        },
        portalLogin() {
            this.$modalComponent(PortalLogin, {}, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Вход на обучающий портал'),
                width: '400px',
            });
        },
    },
    watch: {
        ['$route.name'](name) {
            this.checkRoute(name);
        },
    }
}
</script>
