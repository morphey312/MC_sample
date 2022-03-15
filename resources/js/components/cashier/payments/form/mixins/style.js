export default {
    methods: {
        getCheckSettings() {
            return {
                header: '',
                footer: '',
                styles: `
                    @page {
                        size: 80mm;
                    }
                    html, body {
                        width: 80mm;
                        height: auto;
                        padding: 0;
                        margin: 0;
                        font-family: Arial, sans-serif;
                        font-weight: normal;
                        color: #000;
                        line-height: 16px;
                    }
                    table {
                        width: 100%;
                        font-size: 12px;
                    }
                    .table {
                        border: 1px solid #000;
                        border-spacing: 0;
                        font-size: 12px;
                    }
                    .table th,
                    .table td,
                    .summary td {
                        text-align: left;
                        padding: 5px;
                    }
                    .table td:last-child,
                    .summary td:last-child {
                        text-align: center;
                    }
                    .table td {
                        border-top: 1px solid #000;
                    }
                    .table th:not(:first-child),
                    .table td:not(:first-child) {
                        border-left: 1px solid #000;
                    }
                    .summary td:first-child{
                        text-align: right;
                    }
                    .flex {
                        display: flex;
                    }
                    .flex > div {
                        flex: 1 1 auto;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .underline {
                        text-decoration: underline;
                    }
                    .card-line {
                        border-bottom: 1px solid #000;
                        width: 85%;
                        margin: 10px auto;
                    }
                    .line + .line {
                        margin-top: 10px;
                    }
                `,
            }
        },
    }
}
