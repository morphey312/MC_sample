<template>
    <page
        :title="__('Настройки уведомлений')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-beige shrinkable-tabs">
            <el-tab-pane
                v-if="$canAccess('notification-templates')"
                :lazy="true"
                :label="__('Шаблоны сообщений')"
                name="templates" >
                <section class="shrinkable pt-0">
                    <templates-table />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('notification-channels')"
                :lazy="true"
                :label="__('Каналы связи')"
                name="channels" >
                <section class="shrinkable pt-0">
                    <channels-table />
                </section>
            </el-tab-pane>
            <!--        todo add transcription      -->
            <!--        todo add access      -->
            <el-tab-pane
                v-if="$canAccess('notification-mailing-templates')"
                :lazy="true"
                :label="__('Шаблоны рассылок')"
                name="mailingTemplates" >
                <section class="shrinkable pt-0">
                    <mailing-templates-table />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('notification-mailing-providers')"
                :lazy="true"
                :label="__('Провайдеры рассылок')"
                name="mailingProvider" >
                <section class="shrinkable pt-0">
                    <mailing-providers-table />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import ChannelsTable from './Channels.vue';
import TemplatesTable from './Templates.vue';
import MailingTemplatesTable from './MailingTemplates.vue';
import MailingProvidersTable from './MailingProviders.vue';

export default {
    components: {
        ChannelsTable,
        TemplatesTable,
        MailingTemplatesTable,
        MailingProvidersTable,
    },
    data() {
        return {
            activeTab: this.$canAccess('notification-templates') ? 'templates' : 'channels',
        };
    },
    mounted() {
        console.log(this.$canAccess('notification-mailing-templates'));
        console.log(this.$canAccess('notification-mailing-providers'));
    }
};
</script>
