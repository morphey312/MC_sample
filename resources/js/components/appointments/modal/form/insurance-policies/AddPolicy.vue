<template>
    <div class="sections-wrapper">
        <section class="grey-cap flex-content">
            <policy-list
                ref="table"
                :repository="repository"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons text-right" slot="buttons">
                    <el-button
                        @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        :disabled="activeItem === null"
                        type="primary"
                        @click="select">
                        {{ __('Добавить') }}
                    </el-button>
                </div>
            </policy-list>
        </section>  
    </div>
</template>
<script>
import PolicyList from './List.vue';
import ManageMixin from '@/mixins/manage';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PolicyList,
    },
    props: {
        policies: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                    rows: this.policies,
                });
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        select() {
            this.$emit('selected', this.activeItem);
        },
    }
}   
</script>