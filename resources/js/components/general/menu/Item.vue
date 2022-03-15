<template>
    <el-submenu
        v-if="item.children !== undefined"
        :index="'i' + num">
        <template slot="title">
            <router-link 
                v-if="item.route"
                :to="item.route">
                <menu-label :icon="item.icon">
                    {{ item.title }}
                </menu-label>
            </router-link>
            <menu-label 
                v-else
                :icon="item.icon">
                {{ item.title }}
            </menu-label>
        </template>
        <div class="menu-item" v-for="(child, index) in item.children" :key="num + '-' + index">
            <menu-item
                :item="child"
                :num="'i' + num + '-' + index" />
        </div>
    </el-submenu>
    <span v-else>
        <el-menu-item
            v-if="item.route && item.route.name"
            :route="item.route"
            :index="'i' + num">
            <router-link :to="item.route">
                <menu-label :icon="item.icon">
                    {{ item.title }}
                </menu-label>
            </router-link>
        </el-menu-item>
        <el-menu-item
            v-else-if="item.callback"
            @click="item.callback"
            :index="'i' + num">
            <a href="#" @click.prevent="return false">
                <menu-label :icon="item.icon">
                    {{ item.title }}
                </menu-label>
            </a>
        </el-menu-item>
    </span>
</template>

<script>
import MenuLabel from './Label.vue';

export default {
    name: 'MenuItem',
    components: {
        MenuLabel,
    },
    props: {
        item: {
            type: [Array, Object]
        },
        num: {
            type: [Number, String]
        },
    },
}
</script>