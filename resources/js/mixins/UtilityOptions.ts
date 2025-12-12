import moment from "moment";
import { Vue } from "vue-class-component";
import { camelCase } from "lodash";
import { useStore } from "../store";
import HomepageService from "../service/HomepageService";


// You can declare mixins as the same style as components.
export default class UtilityOptions extends Vue {

    protected store = useStore();

    formatDate(value) {
        let d = "";
        if (value) {
            d = moment(value).format("DD-MM-YYYY");
        }
        return d;
    }
    
    formatMonthDate(value) {
        let d = "";
        if (value) {
            d = moment(value).format("MMM-YY");
        }
        return d;
    }

    get userPermission() {
        const p = JSON.parse(this.store.getters.gerUserPermissions);
        return p;
    }

    getDayName(date) {
        let d = "";

        if (date != "") {
            d = moment(date).format("ddd");
        }

        return d;
    }

    formatTime(value) {
        let d = "";
        if (value) {
            d = moment(value, "HH:mm").format("hh:mm A");
        }
        return d;
    }

    formatAmount(value) {
        value = Number(value);
        return value.toFixed(2);
    }

    camelizeKeys = obj => {
        if (Array.isArray(obj)) {
            return obj.map(v => this.camelizeKeys(v));
        } else if (obj !== null && obj.constructor === Object) {
            return Object.keys(obj).reduce(
                (result, key) => ({
                    ...result,
                    [camelCase(key)]: this.camelizeKeys(obj[key])
                }),
                {}
            );
        }
        return obj;
    };

    can(permission) {
        const pL = this.userPermission;
        const f = pL.filter(e => e.name == permission);

        let res = false;

        if (f.length > 0) {
            res = true;
        }

        return res;
    }

    convertToFull(t) {
        let n = "";

        if (t == "REF") {
            n = "Receive Funds";
        } else if (t == "DPT") {
            n = "Bank Deposit";
        } else if (t == "EXP") {
            n = "Bank Expense";
        } else if (t == "FTR") {
            n = "Funds Transfer";
        } else if (t == "CHQ") {
            n = "Cheque";
        } else if (t == "INE") {
            n = "Sales Invoice";
        } else if (t == "RFD") {
            n = "Sales Refund";
        } else if (t == "TRN") {
            n = "Stock Transfer";
        } else if (t == "PUR") {
            n = "Stock Purchase";
        } else if (t == "RPU") {
            n = "Purchase Stock Return";
        } else if (t == "SLS") {
            n = "Sales Receipt";
        } else if (t == "INV") {
            n = "Invoice Receipt";
        } else if (t == "RFR") {
            n = "Refund Receipt";
        } else if (t == "CRV") {
            n = "Credit Voucher";
        } else if (t == "DBV") {
            n = "Debit Voucher";
        } else if (t == "JRV") {
            n = "Journal Voucher";
        } else if (t == "EXV") {
            n = "Expense Voucher";
        } else if (t == "OPB") {
            n = "User Opening balance";
        }

        return n;
    }

    formatDateTime(date) {
        return moment(date).format("DD-MM-YYYY hh:mm A");
    }

    printReceiptUtil() {
        window.print();
    }
}
