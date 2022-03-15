<template>
    <note-form :model="model" :edit-field="field">
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
    </note-form>
</template>

<script>
import Note from '@/models/appointment/note';
import NoteForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        NoteForm,
    },
    props: {
        note: Object,
        appointment_id: Number,
        field: String,
    },
    data() {
        return {
            model: new Note(),
        }
    },
    mounted() {
        this.model.set('appointment_id', this.appointment_id);
        if (!_.isNull(this.note)) {
            this.model.set('id', this.note.id);
            this.model.set('task', this.note.task);
            this.model.set('note', this.note.note);
        }
    }
}
</script>
