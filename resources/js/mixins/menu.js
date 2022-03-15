export default {
    methods: {
        accessFilter(items) {
            return items.filter((item) => {
                if (item.visible === false) {
                    return false;
                }

                if (item.permission !== undefined && !this.$can(item.permission)) {
                    return false;
                }
                if (item.children !== undefined) {
                    item.children = this.accessFilter(item.children);
                    if (item.children.length === 0) {
                        delete item.children;
                    }
                }
                return item.children !== undefined || item.route !== undefined || item.callback !== undefined;
            });
        },
    },
}