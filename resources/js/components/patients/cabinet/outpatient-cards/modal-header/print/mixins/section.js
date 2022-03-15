import {Section, Line} from '@/services/card/template';
import FormLine from '../Line.vue';

export default {
    components: {
        FormLine,
    },
    methods: {
        isSection(thing) {
            return thing instanceof Section;
        },
        isLine(thing) {
            return thing instanceof Line;
        },
    },
};