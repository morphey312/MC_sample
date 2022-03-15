<template>
    <div class="sections-wrapper">
        <section class="grey-cap flex-content">
            <card-list
                ref="table"
                :repository="repository"
                :patient="patient"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        :disabled="activeItem === null"
                        @click="select">
                        {{ __('Добавить карту') }}
                    </el-button>
                </div>
            </card-list>
        </section>  
    </div>
</template>
<script>
import CardList from './List.vue';
import ManageMixin from '@/mixins/manage';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CardList,
    },
    props: {
        patient: Object,
        cards: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                            rows: this.cards,
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