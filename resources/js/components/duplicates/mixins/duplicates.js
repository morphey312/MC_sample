import GroupsTable from '../Table.vue';

export default {
    components: {
        GroupsTable,
    },
    data() {
        return {
            groups: [],
            loading: true,
            page: 1,
            chunkSize: 50,
        };
    },
    methods: {
        loadPrev() {
            if (this.page > 1) {
                this.page--;
                this.loadDuplicates();
            }
        },
        loadNext() {
            if (this.groups.length === this.chunkSize) {
                this.page++;
                this.loadDuplicates();
            }
        },
        createGroups(result) {
            let groups = [];
            result.forEach((group) => {
                let others = {};
                let main = this.pickMainItem(group);
                group.forEach((item) => {
                    others[item.id] = item.id !== main;
                });
                groups.push({
                    main,
                    others,
                    items: group,
                });
            });
            return groups;
        },
        getGroupsToMerge() {
            let toMerge = {};
            this.groups.forEach((group) => {
                if (group.main !== null) {
                    let others = [];
                    Object.keys(group.others).forEach((key) => {
                        if (group.others[key] && key !== group.main) {
                            others.push(key);
                        }
                    });
                    if (others.length !== 0) {
                        toMerge[group.main] = others;
                    }
                }
            });
            return toMerge;
        },
        merge() {
            let toMerge = this.getGroupsToMerge();
            if (Object.keys(toMerge).length !== 0) {
                this.loading = true;
                return this.repository.merge(toMerge).then(() => {
                    this.loading = false;
                    this.loadDuplicates();
                    return true;
                });
            }
            return Promise.resolve(false);
        },
    },
};