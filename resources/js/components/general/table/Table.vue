<script>
import { Vuetable } from 'vuetable-2';

export default {
    extends: Vuetable,
    props: {
        headerFilter: {
            type: Object,
            default: () => ({}),
        },
    },
    methods: {
        setData(data) {
            Vuetable.methods.setData.call(this, data);
            this.$nextTick(() => {
                this.bindTooltips();
            });
        },
        bindTooltips() {
            let tdList = this.$el.querySelectorAll('tbody td,.ellipsis');
            tdList.forEach((td) => {
                if (td.getAttribute('ellipsis') === 'true') {
                    if (td.offsetWidth < td.scrollWidth) {
                        td.setAttribute('title', td.innerText);
                    } else {
                        td.removeAttribute('title');
                        td.removeAttribute('ellipsis');
                    }
                } else if (td.offsetWidth < td.scrollWidth) {
                    td.setAttribute('title', td.innerText);
                    td.setAttribute('ellipsis', 'true');
                }
            });
        },
    },
};
</script>