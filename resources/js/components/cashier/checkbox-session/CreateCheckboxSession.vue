<template>
    <checkbox-session-form
        v-loading="loading"
        :checkbox-cashboxes="checkboxCashboxes"
        :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="loading"
                @click.prevent="create" >
                {{ __('Открыть смену') }}
            </el-button>
        </div>
    </checkbox-session-form>
</template>

<script>
import CheckboxSessionForm from './Form.vue';
import checkbox from '@/services/checkbox';
import Shift from "@/models/checkbox/shift";
import CheckboxShiftRepository from "@/repositories/cashbox/shift";

export default {
    components: {
        CheckboxSessionForm,
    },
    props: {
        shifts : {
            type: Array,
            default: () => {},
        },
        checkboxCashboxes : {
            type: Array,
            default: () => {},
        },
    },
    data() {
        return {
            model: new Shift(),
            checkboxRepository: new CheckboxShiftRepository(),
            loading: false,
        }
    },
    methods: {
        create() {
            this.loading = true;
            this.checkboxRepository.fetch({
                money_reciever_cashbox: this.model.money_reciever_cashbox_id,
            }).then((response => {
                if (response.rows.length) {
                    let shift = response.rows.find((item) => item.money_reciever_cashbox_id === this.model.money_reciever_cashbox_id);
                    checkbox.login(this.model.login,this.model.password,this.model.cashbox_key).then((res) => {
                        shift.access_token = res.access_token;
                        shift.employee_id = this.$store.state.user.employee_id;
                        shift.save().then(() => {
                            this.$emit('openSession');
                            this.loading = false;
                        }).catch((e) => {
                            this.$displayErrors(e);
                            this.loading = false;
                        });
                    }).catch((e) => {
                        this.$error(e.response.data.message);
                        this.loading = false;
                    })
                } else {
                    checkbox.login(this.model.login,this.model.password,this.model.cashbox_key).then((res) => {
                        new Shift({
                            money_reciever_cashbox_id: this.model.money_reciever_cashbox_id,
                            employee_id: this.$store.state.user.employee_id,
                            access_token: res.access_token,
                        }).save().then(() => {
                            this.$emit('openSession');
                            this.loading = false;
                        }).catch((e) => {
                            this.$displayErrors(e);
                            this.loading = false;
                        });
                    }).catch((e) => {
                        this.$error(e.response.data.message);
                        this.loading = false;
                    })
                }
            }))
        },
        cancel() {
            this.$emit('close');
            this.loading = false;
        }
    },
}
</script>
