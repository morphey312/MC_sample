<template>
    <div class="paste-special">
        {{ __('Специальная вставка:') }}
        <a 
            v-for="(paste, index) in pastes" 
            :key="paste.key"
            @click.prevent="pasteSpecial(paste.key)">
            <span v-if="index !== 0">,</span>
            {{paste.label}}
        </a>
    </div>
</template>

<script>
export default {
    props: {
        editor: Object,
    },
    data() {
        return {
            pastes: [
                {key: 'patientName', label: __('Имя пациента')},
                {key: 'analysisName', label: __('Название анализа')},
            ],
        };
    },
    methods: {
        pasteSpecial(key) {
            this.editor.$refs.content.focus();
            this.editor.exec('insertHTML', '{' + key + '}', true);
        },
    },
}
</script>>

