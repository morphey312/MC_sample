<template>
    <alert
        v-if="errors.length" 
        type="attention">
        <p>{{ title }}</p>
        <ul>
            <li 
                v-for="(error, index) in errors"
                :key="index">
                {{ error }}
            </li>
        </ul>
    </alert>
</template>

<script>
export default {
    props: {
        catch: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: () => __('При отправке формы возникли ошибки'),
        },
    },
    data() {
        return {
            errors: [],
        };
    },
    mounted() {
        this.$eventHub.$on('validationErrors', this.showErrors);
    },
    beforeDestroy() {
        this.$eventHub.$off('validationErrors', this.showErrors);
    },
    methods: {
        showErrors(errors) {
            if (errors !== undefined) {
                let matches = [];
                Object.keys(errors).forEach((key) => {
                    let match = false;
                    for (let target of this.catch) {
                        if (typeof target.key === 'string') {
                            if (target.key === key) {
                                match = target;
                                break;
                            }
                        } else if (target.key instanceof Array) {
                            if (target.key.indexOf(key) !== -1) {
                                match = target;
                                break;
                            }
                        } else if (target.key instanceof RegExp) {
                            if (target.key.test(key)) {
                                match = target;
                                break;
                            }
                        }
                    }
                    if (match !== false) {
                        errors[key].forEach((error) => {
                            matches.push(`${match.label}: ${error}`);
                        });
                    }
                });
                this.errors = matches;
            }
        }
    }
}
</script>