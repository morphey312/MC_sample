<template>
    <div
        v-if="entries.length !== 0"
        class="diary-records paragraph">


        <div
            v-for="(record, index) in entries"
            :key="index"
            class="diary-entry diary-col">


            <div class="comment cond-record" v-if="!record.editing" v-text="'t: ' + record.temperature + ' ℃, '"></div>
            <div class="comment__editing condition-record-editing" v-if="record.editing !== undefined && record.editing">
                <label>{{ __('t: ') }}</label>
                <form-input
                placeholder=" "
                    :autosize="true"
                    :entity="record"
                    property="temperature"
                />
            </div>

            <div class="comment cond-record" v-if="!record.editing" v-text="__('АД: ') + record.at + ' / ' + record.at2 + ' мм рт. ст, '"></div>
            <div class="comment__editing condition-record-editing" v-if="record.editing !== undefined && record.editing">
                <label>{{ __('АД: ') }}</label>
                <form-input
                    placeholder=""
                    :autosize="true"
                    :entity="record"
                    property="at"
                />
                /
                <form-input
                    placeholder=" "
                    :autosize="true"
                    :entity="record"
                    property="at2"
                />
            </div>

            <div class="comment cond-record" v-if="!record.editing" v-text="__('ЧП: ') + record.frequency + ' ' + __('уд./мин')"></div>
            <div class="comment__editing condition-record-editing" v-if="record.editing !== undefined && record.editing">
                <label>{{ __('ЧП: ') }}</label>
                <form-input
                    :autosize="true"
                    :placeholder="__('ЧП: ')"
                    :entity="record"
                    property="frequency"
                />
            </div>



            <div class="actions__wrap" v-show="!readonly">
                <div class="action__edit" @click="editConditionEntry(record)" >
                    <svg-icon v-if="!record.editing"
                        name="edit"
                        class="icon-small icon-blue"/>
                    <svg-icon v-if="record.editing" @click="updateConditionEntry(record)"
                              name="check"
                              class="icon-small icon-blue"/>
                </div>
                <div class="action__delete"
                     @click="deleteConditionEntry(record)">
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
        deleteConditionEntry(conditionEntryRecord) {
            this.$clearErrors();

            conditionEntryRecord.delete().then((response) => {
                this.entries = this.entries.filter((record)=>{
                    return record !== conditionEntryRecord;
                });

                this.$info(__('Запись была успешно удалена'));

            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        editConditionEntry(conditionEntryRecord) {
            conditionEntryRecord.set('editing', true);
        },
        updateConditionEntry(conditionEntryRecord){
            this.$clearErrors();

            conditionEntryRecord.save().then((response) => {
                this.$info(__('Запись была успешно изменена'));
                conditionEntryRecord.editing = false;
            }).catch((e) => {
                this.$displayErrors(e);
            });
        }
    },
}
</script>
