export default {
    data() {
        return {
            expandedGroups: [],
            expandedRightGroups: [],
        };
    },
    methods: {
        makeGrops(data, expandedGroups) {
            let result = [];
            let groups = _.groupBy(data, 'group_id');
            let sorted = _.sortBy(Object.keys(groups), (key) => groups[key][0].group);
            sorted.forEach((groupId) => {
                let expanded = true;
                if (groupId) {
                    expanded = expandedGroups.indexOf(groupId) !== -1;
                    let group = {
                        id: `g:${groupId}`,
                        value: groups[groupId][0].group,
                        group_id: groupId,
                        _is_group: true,
                        _is_expanded: expanded,
                    };
                    result.push(group);
                }
                if (expanded) {
                    for (let item of groups[groupId]) {
                        result.push(item);
                    }
                }
            });
            return result;
        },
        toggleGroup(groupId, target) {
            if (target.indexOf(groupId) === -1) {
                target.push(groupId);
            } else {
                _.pull(target, groupId);
            }
        },
        expandGroup(groupId, target) {
            if (target.indexOf(groupId) === -1) {
                target.push(groupId);
            }
        },
        collapseGroup(groupId, target) {
            if (target.indexOf(groupId) !== -1) {
                _.pull(target, groupId);
            }
        },
        checkGroupItems(groupId, items, table) {
            items.forEach((item) => {
                if (item.group_id == groupId) {
                    table.selectId(item.id);
                }
            });
        },
        uncheckGroupItems(groupId, items, table) {
            items.forEach((item) => {
                if (item.group_id == groupId) {
                    table.unselectId(item.id);
                }
            });
        },
        uncheckGroup(groupId, table) {
            table.unselectId(`g:${groupId}`);
        },
        getAllGroupIds(items) {
            return items.filter((item) => item._is_group).map((item) => item.group_id);
        },
        checkAllItems(items, table) {
            table.selectedTo = items.map((item) => item.id);
        },
        uncheckAllItems(table) {
            table.selectedTo = [];
        },
    },
};