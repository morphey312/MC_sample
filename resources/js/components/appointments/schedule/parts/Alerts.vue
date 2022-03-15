<template>
    <alert 
        type="info"
        v-if="message.length != 0">
        {{ message }}
    </alert>
</template>

<script>
export default {
    data() {
        return {
            blockList: {},
            alertDiff: 160,
            message: '',
        }       
    },
    created() {
        this.updateTimersListener = () => {
            this.updateTimers();
        };

        this.listenAddLockAlert = (data) => {
            this.addToList(data);
        }
    },
    mounted(){
        this.$ticker.on(this.updateTimersListener, 2);
        this.$eventHub.$on('add-lock-alert', this.listenAddLockAlert);
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimersListener);
        this.$eventHub.$off('add-lock-alert', this.listenAddLockAlert);
    },
    methods: {
        updateTimers() {
            if(!_.isEmpty(this.blockList)) {
                let timeouts = '';
                let dates = {};

                _.each(this.blockList, (group) => {
                    _.each(group.locks, (lock) => {
                        if(lock.type !== 'fixed' && this.isActiveTimeDiff(lock.created_at)){
                            timeouts += this.$formatter.dateFormat(group.date);
                            timeouts += __(' С {start} до {end}.', {start: lock.start, end: lock.end});
                        }
                    });
                });

                if(timeouts.length != 0) {
                    this.message = __('Заканчивается время блока:') + ' ' + timeouts;
                    return;    
                }
            }

            this.message = '';
        },
        isActiveTimeDiff(timestamp) {
            return this.$moment().diff(this.$moment(timestamp), 'seconds') >= this.alertDiff;
        },
        addToList(data) {
            let listId = data.id;

            if(data.locks.length > 0){
                this.blockList[listId] = {
                    locks: data.locks,
                    date: data.date,
                };
            } else {
                delete this.blockList[listId];
            }
        }
    },
}   
</script>