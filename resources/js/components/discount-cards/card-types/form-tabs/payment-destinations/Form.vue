<template>
    <div class="sections-wrapper">
        <form>
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-select 
                            :entity="model"
                            :options="destinations"
                            property="payment_destination_id"
                            :label="__('Назначения платежа')"
                            :filterable="true"
                        />
                        <form-date 
                            :entity="model"
                            property="date_start"
                            :label="__('Начало действия скидки')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-input 
                            :entity="model"
                            property="discount_percent"
                            :step="1"
                            :min="1"
                            :max="100"
                            type="number"
                            :label="__('Процент скидки')"
                        />
                        <form-date 
                            :entity="model"
                            property="date_end"
                            :label="__('Завершение действия скидки')"
                        />
                    </el-col>
                </el-row>
            </section>
            <slot name="buttons"></slot>
        </form>
    </div>    
</template>
<script>
export default {
    props: {
        destinations: Array,
        model: Object,
    },
}   
</script>