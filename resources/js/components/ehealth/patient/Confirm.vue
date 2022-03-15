<template>
   <div>
       <div class="p-20">
           <div v-html="consent" />
       </div>
       <hr />
       <div class="p-20">
           <el-checkbox
               v-model="process_disclosure_data_consent" >
               {{ checkbox }}
           </el-checkbox>
       </div>
       <div class="dialog-footer text-right">
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
   </div>
</template>

<script>
export default {
    props: {
        consent: String,
        checkbox: String
    },
    data () {
        return {
            process_disclosure_data_consent: false
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            if (this.process_disclosure_data_consent !== false) {
                this.$emit('save', this.process_disclosure_data_consent);
            } else {
                this.$error(__('Вы обязаны предоставить информационная памятку пациенту'));
            }
        },
    }
};
</script>
