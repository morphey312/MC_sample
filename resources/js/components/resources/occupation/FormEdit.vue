<template>
    <occupation-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </occupation-form>
</template>
<script>
import OccupationForm from './Form.vue';
import Occupation from '@/models/department/room/occupation';

export default {
    components: {
        OccupationForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.getModel(),
        }
    },
    methods: {
        getModel() {
            let model = this.item.model.clone();
            model.start = model.start.substr(0, 5);
            model.end = model.end.substr(0, 5);
            return model;
        },
        cancel() {
            this.$emit('cancel');
        },
        save() {
            if (this.invalidDuration()) {
                return this.$error(__('Измените период'));
            }

            this.model.save().then(() => {
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        invalidDuration() {
            let start = this.$moment(`${this.model.date_from} ${this.getTimeString(this.model.start)}`);
            let end = this.$moment(`${this.model.date_to} ${this.getTimeString(this.model.end)}`);
            return start.isSameOrAfter(end);
        },
        getTimeString(time) {
            if (time.length === 5) {
                time += ":00";
            }
            return time;
        },
    },
}
</script>