import {Section, Line, Sketchpad} from '@/services/card/template';
import FormLine from '../Line.vue';

export default {
    components: {
        FormLine,
    },
    methods: {
        isSection(thing) {
            return thing instanceof Section;
        },
        isSketchpad(thing){
            return thing instanceof Sketchpad;
        },
        isLine(thing) {
            return thing instanceof Line;
        },
    },
};
