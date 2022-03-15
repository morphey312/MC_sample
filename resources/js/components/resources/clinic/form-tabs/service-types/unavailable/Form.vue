<template>
    <form>
        <form-row 
            name="from" 
            :label="__('С')">
            <div class="form-input-group">
                <form-date
                    :entity="model"
                    property="date_from" />
                <form-time
                    :entity="model"
                    start="00:00"
                    end="23:50"
                    step="00:10"
                    property="time_from" />
            </div>
        </form-row>
        <form-row 
            name="from" 
            :label="__('По')">
            <div class="form-input-group">
                <form-date
                    :entity="model"
                    property="date_to" />
                <form-time
                    :entity="model"
                    start="00:00"
                    end="23:50"
                    step="00:10"
                    property="time_to" />
            </div>
        </form-row>
        <form-text
            :entity="model"
            property="comment" 
            :label="__('Комментарий')" />
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="save">
                {{ isCreate ? __('Добавить') : __('Обновить') }}
            </el-button>
        </div>
    </form>
</template>

<script>
export default {
    props: {
        model: Object,
        isCreate: {
            type: Boolean,
            default: false,
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else if (this.isCreate) {
                    this.$emit('created', this.model);
                } else {
                    this.$emit('saved', this.model);
                }
            });
        },
    },
}
</script>