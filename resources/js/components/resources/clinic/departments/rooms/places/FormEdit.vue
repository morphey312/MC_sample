<template>
    <place-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </place-form>
</template>
<script>
import Place from '@/models/department/room/place';
import PlaceForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        PlaceForm,
    },
    props: {
        item: Object,
        room: Object,
        activeIndex: null,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        update() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if (this.numberPresent()) {
                    return this.$error(__('Такой номер в плате уже существует'));
                }

                if (_.isEmpty(errors)) {
                    this.$info(__('Койка успешно изменена'));
                    this.$emit('saved', this.model); 
                    return;
                }
                return this.$displayErrors({errors});
            });
        },
        numberPresent() {
            if (this.activeIndex === null) {
                return false;
            }
            let numberIndex = this.room.places.findIndex(item => item.number === this.model.number);
            if (numberIndex === -1) {
                return false;
            }
            return numberIndex != this.activeIndex;
        }
    },
}
</script>