<template>
    <div
        v-if="entries.length !== 0"
        class="diary-records paragraph">
        <h3 v-if="title">
            {{ title }}
        </h3>

        <div
            v-for="(record, index) in entries"
            :key="index"
            class="diary-entry">
            <div class="comment" v-if="!record.editing" v-text="record.comment"></div>
            <div class="comment__editing" v-if="record.editing !== undefined && record.editing">
                <form-text
                    :autosize="true"
                    :entity="record"
                    property="comment"
                />
            </div>
            <div class="actions__wrap" v-show="!readonly">
                <div class="action__edit" @click="editServiceEntry(record)" >
                    <svg-icon v-if="!record.editing"
                        name="edit"
                        class="icon-small icon-blue"/>
                    <svg-icon v-if="record.editing" @click="updateServiceEntry(record)"
                              name="check"
                              class="icon-small icon-blue"/>
                </div>
                <div class="action__delete"
                     @click="deleteServiceEntry(record)">
                    <svg-icon
                        name="delete"
                        class="icon-small icon-blue"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        records: Array,
        title: {
            type: [Boolean, String],
            default: __('Служебные записи врача'),
        },
        readonly: {
            type: [Boolean, String],
            default: true,
        }    },
    watch: {
        records: {
            handler (val) { this.entries = val },
            immediate: true
        },
    },
    data(){
        return {
            entries: []
        }
    },
    methods: {
        deleteServiceEntry(serviceEntryRecord) {
            this.$clearErrors();

            serviceEntryRecord.delete().then((response) => {
                this.entries = this.entries.filter((record)=>{
                    return record !== serviceEntryRecord;
                });

                this.$info(__('Запись была успешно удалена'));

            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        editServiceEntry(serviceEntryRecord) {
            serviceEntryRecord.set('editing', true);
        },
        updateServiceEntry(serviceEntryRecord){
            this.$clearErrors();

            serviceEntryRecord.save().then((response) => {
                this.$info(__('Запись была успешно изменена'));
                serviceEntryRecord.editing = false;
            }).catch((e) => {
                this.$displayErrors(e);
            });
        }
    },
}
</script>
