<template>
    <div>
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="paper_referral_requester_employee_name"
                    :label="__('Автор направлення')"/>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="paper_referral_requisition"
                    :label="__('Номер направлення')"/>
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="model"
                    property="paper_referral_service_request_date"
                    :label="__('Дата направлення')"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="paper_referral_requester_legal_entity_edrpou"
                    :label="__('ЕГРПОУ НМП')"/>
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="paper_referral_requester_legal_entity_name"
                    :label="__('Название НМП')"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-text
                    :entity="model"
                    property="paper_referral_note"
                    :label="__('Заметки')"/>
            </el-col>
        </el-row>
    </div>
</template>

<script>
export default {
    props: {
        model: Object
    },
}
</script>
