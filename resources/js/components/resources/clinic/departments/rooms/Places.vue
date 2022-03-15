<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <place-list 
                ref="table"
                :room="room"
                @selection-changed="setActiveItem"
                @loaded="refreshed" />
            <div class="mt-20">
                <el-button
                    :disabled="activeItem === null"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
            </div>
        </section>
         <hr />
        <section>
            <place-form :model="model">
                <div 
                    slot="buttons"
                    class="mt-20">
                    <el-button
                        @click="add">
                        {{ __('Добавить койку') }}
                    </el-button>
                </div>
            </place-form>
        </section>
    </div>
</template>

<script>
import Room from '@/models/department/room';
import RoomPlace from '@/models/department/room/place';
import PlaceForm from './places/Form.vue';
import FormEdit from './places/FormEdit.vue';
import PlaceList from './places/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PlaceForm,
        PlaceList,
    },
    props: {
        room: Object,
    },
    data() {
        return {
            model: new RoomPlace({
                room_id: this.room.id,
            }),
            activeIndex: null,
        };
    },
    methods: {
        setActiveItem({selection, index}) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
            this.activeIndex = index;
        },
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.savePlace(() => {
                this.model = new RoomPlace({
                    room_id: this.room.id,
                });
                this.$info(__('Койка успешно добалена в палату'));
                this.refresh();
            });
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить койку в палате'),
                editProps: {
                    room: this.room,
                    activeIndex: this.activeIndex,
                },
                width: '600px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные койки в палате успешно обновлены'),
            };
        },
        savePlace(then) {
            this.$emit('place-created', new RoomPlace(this.model.attributes));
        },
        onUpdated(place) {
            this.$emit('place-updated', {place, index: this.activeIndex});
        },
    },
}
</script>
