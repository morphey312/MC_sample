<template>
    <div class="chat-dialog" v-loading="loading">
        <div class="chat-body" id="chat-body">
            <el-row :gutter="20" class="chat-body__item" v-for="(message, index) in messages" :key="index">
                <el-col :span="6" class="chat-body__item-user">
                    <div class="chat-body__item-name">{{ message.employee }}</div>
                    <div class="chat-body__item-time">{{ message.created_at }}</div>
                </el-col>
                <el-col class="chat-body__item-message" :span="18">
                    <span class="chat-body__item-text"> {{ message.text }}</span>
                </el-col>
            </el-row>
        </div>

        <div class="buttom mt-10">
            <el-input
                v-model="message"
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить текст')"
            />
            <div class="mt-10 text-right">
                <el-button
                    style="mt-10"
                    @click="send">
                    {{ __('Отправить') }}
                </el-button>
            </div>
        </div>
    </div>
</template>

<script>
import Room from '@/models/chat/room';
import ChatUserList from './ChatUserList.vue'
import Message from '@/models/chat/message';
import broadcast from '@/services/broadcast';
import CONSTANTS from "@/constants";

export default {
    props: {
        room_id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            model: new Room({id: this.room_id}),
            loading: true,
            message: '',
            messages: [],
        }
    },
    computed: {
         channel(){
             return '.App.Chat.Room' + this.room_id;
         }
    },
    beforeDestroy() {
        document.removeEventListener('keyup', this.onKeydown, false);
    },
    mounted() {
        document.addEventListener('keyup', this.onKeydown);
        this.model.fetch(['employees','messages']).then((response)=> {
            this.messages = response.response.data.messages;
            this.scrollToLastMessage();
            this.loading = false;
        });
        broadcast.subscribeChat((event, data) => {
                this.readMessage(data);
            }, (channel) => {
                this.$error(__('Не удалось подписаться на канал сообщений'));
            },
            this.room_id);

        this.$eventHub.$on('new_chat_message', this.readMessage);

    },
    beforeDestroy() {
        broadcast.leaveChatChannel(this.channel);
    },
    methods: {
        onKeydown(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                this.send();
            }

        },
         settings() {
            this.$modalComponent(ChatUserList, {
                    model: this.model,
                }, {
                    close: (dialog) => {
                        dialog.close();
                    },
                }, {
                    header: __('Обсуждение'),
                    width: '600px',
                    customClass: 'no-footer',
                });
        },
        readMessage(message) {
            this.messages.push(message.data);
            this.loading = false;
        },
        scrollToLastMessage() {
            setTimeout(() => {
                let lastMsg = document.querySelectorAll(".chat-body__item");
                if (lastMsg.length > 1) {
                    lastMsg[lastMsg.length - 1].scrollIntoView(true);
                }
            }, 200);
        },
        send() {
            if (this.message.length > 3) {
                this.loading = true;
                new Message({
                    room_id: this.room_id,
                    employee_id: this.$store.state.user.employee_id,
                    click_action: CONSTANTS.EVENT_ACTIONS.SHOW_MODAL_AGREEMENT_ACT,
                    text: this.message,
                    notificationMessage:  __('Вы получили новое сообщение в чате по акту согласования №{act_number} от пользователя {fullname}', {
                        act_number: this.room_id,
                        fullname: this.$store.state.user.employee.full_name
                        })
                    }).save().then((response) => {
                        this.message = '';
                         this.loading = false;
                    });
            } else {
                this.$info(__('Сообщение должно быть не меньше 3-х символов'));
            }
        },
    }
}
</script>
