<template>
    <country-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </country-form>
</template>

<script>
import CountryForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import Country from '@/models/country';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        CountryForm,
    },
    data() {
        return {
            model: new Country(),
        }
    },
}
</script>
