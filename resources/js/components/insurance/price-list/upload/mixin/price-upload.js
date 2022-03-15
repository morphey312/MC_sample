export default {
    methods: {
        upload() {
            this.saving = true;
            this.savingMessage = __('Сохранение...');
            this.savePrices().then(() => {
                this.saving = false;
                this.resetState();
                this.$info(__('Новые тарифы были успешно сохранены'));
            }).catch(() => {
                this.saving = false;
                this.$error(__('Не удалось сохранить новые тарифы'));
            });
        },
        getValidRows() {
            return this.rows.filter(row => {
                return !row.service.isNew() && !row.service.disabled;
            });
        },
        savePrices() {
            let request = this.getPricesBatchRequest();
            this.savingMessage = __('Обновление тарифов...');
            let rows = this.getValidRows();
            rows.forEach((row) => {
                if (row.price.isNew()) {
                    row.price.service_id = row.service.id;
                    request.create(row.price);
                } else if (this.isClosed(row.price)) {
                    request.update(row.price);
                } else if (this.isUpdatedPrice(row.price)) {
                    let oldPrice = row.price;
                    row.price = new Price({
                        cost: row.price.cost,
                        self_cost: row.price.self_cost,
                        date_from: row.price.date_from,
                        set_id: row.price.set_id,
                        currency: row.price.currency,
                        clinics: [this.input.clinic],
                    });
                    row.price.service_id = row.service.id;
                    oldPrice.reset();
                    request.create(row.price);
                }
            });
            if (request.isNotEmpty) {
                return request.submit().then((result) => {
                    if (result.failure.length !== 0) {
                        return Promise.reject({
                            invalid: result.failure,
                        });
                    }
                }).catch((error) => {
                    this.$error(__('Не удалось сохранить некоторые тарифы'));
                    if (error.invalid !== undefined) {
                        this.setFailedPrices(error.invalid);
                    }
                    return Promise.reject(error);
                });
            } else {
                return Promise.resolve();
            }
        },
    }
}