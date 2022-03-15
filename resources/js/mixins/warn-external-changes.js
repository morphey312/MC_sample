export default {
    mounted() {
        let model = this.getModelToWatch();
        model.watchExChanges();
        model.on('changedExternally', () => {
            this.$warning(__('Запись, которую вы редактируете, была изменена другим пользователем! Во избежание потери данных, обновите редактируемую запись.'));
        });
    },
    beforeDestroy() {
        this.getModelToWatch().stopWatchExChanges();
    },
    methods: {
        confirmExternalOverwrite(callback) {
            if (this.getModelToWatch().changedExternally) {
                this.$confirm(__('Эта запись была изменена другим пользователем. Пересохранение этой записи может привести к тому, что  изменения другого пользователя будут потеряны. Вы уверены что хотите сохранить эту запись?'), callback);
            } else {
                callback();
            }
        },
        getModelToWatch() {
            return this.model;
        },
    },
}