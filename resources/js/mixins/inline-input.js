let tabIndexList = [];

export default {
    props: {
        required: {
            type: Boolean,
            default: false,
        },
        tabIndex: {
            type: [Number, Boolean],
            default: false,
        },
        formatter: {
            type: Function,
        },
        validator: {
            type: [Function, String],
        },
    },
    data() {
        return {
            val: null,
            editMode: false,
        };
    },
    created() {
        this.addToTabIndexList();
    },
    beforeDestroy() {
        this.removeFromTabIndexList();
    },
    methods: {
        edit() {
            this.val = this.value;
            this.editMode = true;
        },
        confirm(moveFocus = true) {
            if (this.isValid()) {
                this.$emit('input', this.val);
                this.editMode = false;
                if (moveFocus) {
                    this.nextTabIndex();
                }
                return true;
            }
            return false;
        },
        revert() {
            this.editMode = false;
        },
        isValid() {
            if (this.required === false || !this.isEmpty()) {
                return this.validator !== undefined 
                    ? this.validator(this.val)
                    : true;
            }
            return false;
        },
        isEmpty() {
            return _.isArray(this.val) 
                ? this.val.length === 0
                : (this.val === '' || this.val === null || this.val === undefined);
        },
        addToTabIndexList() {
            if (this.tabIndex !== false) {
                if (tabIndexList.indexOf(this) === -1) {
                    tabIndexList.push(this);
                    tabIndexList = tabIndexList.sort((a, b) => {
                        return a.tabIndex - b.tabIndex;
                    });
                }
            }
        },
        removeFromTabIndexList() {
            if (this.tabIndex !== false) {
                _.pull(tabIndexList, this);
            }
        },
        nextTabIndex() {
            if (this.tabIndex !== false) {
                let index = tabIndexList.indexOf(this);
                if (index !== -1) {
                    if (index < tabIndexList.length - 1) {
                        tabIndexList[index + 1].focusByTab();
                    } else {
                        tabIndexList[0].focusByTab();
                    }
                }
            }
        },
        focusByTab() {
            this.edit();
        },
    },
};

const focusAt = (index) => {
    for (let i = 0; i < tabIndexList.length; i++) {
        if (tabIndexList[i].tabIndex === index) {
            tabIndexList[i].focusByTab();
        }
    }
}

export {focusAt};