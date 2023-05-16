

export default {
    data() {
        return {
        };
    },
    methods: {
        numberWithCommas(x) {
            return new Intl.NumberFormat("en-IN").format(x);
            // return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
    }
};
