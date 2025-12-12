<template>
  <div class="history-set-height">
      <h4 class="checkout-heading">
        <i class="pi pi-file-o"></i><b> Order ID : {{ receiptID }} </b>
      </h4>
      <h5 class="mt-3 mb-3 text-center">
        Invoice Payment Methods
      </h5>
        <table class="table table-bordered table-striped table-payments">
          <thead v-for="(process, index) in receiptPayments" :key="process">
              <tr>
                  <th colspan="2">
                      <h4 class="checkout-heading">
                          <i class="pi pi-money-bill"></i> Transaction No {{ index + 1}}  <span class="payment-source">Source: {{process.transSource}}</span>
                      </h4>
                  </th>
              </tr>
              <tr>
                  <th>Status</th>
                  <th>{{process.transStatus}}</th>
              </tr>
              <tr>
                  <th>Payment Method</th>
                  <th>{{process.paymentType}}</th>
              </tr>
              <tr>
                  <th>Acc No</th>
                  <th>{{process.accountNo}}</th>
              </tr>
              <tr>
                  <th>Date/Time</th>
                  <th>{{formatDate(process.transDate)}} | {{formatTime(process.transTime)}}</th>
              </tr>
              <tr>
                  <th>Total Amount</th>
                  <th>${{fixLength(process.transTotalAmount)}}</th>
              </tr>
              <tr>
                  <th>Tendered</th>
                  <th v-if="process.tendered">${{fixLength(process.tendered)}}</th>
              </tr>
              <tr>
                  <th>Change</th>
                  <th v-if="process.change">${{fixLength(process.change)}}</th>
              </tr>
              <tr>
                  <th>Round Off</th>
                  <th v-if="process.roundOff">${{fixLength(process.roundOff)}}</th>
              </tr>
              <tr>
                  <th>Terminal ID</th>
                  <th>{{process.terminalId}}</th>
              </tr>
          </thead>
        </table>
        <h4 class="checkout-heading">
          <i class="pi pi-money-bill"></i> Total Payments  : $ {{totalAmount()}}
        </h4>
  </div>
</template>

<script lang="ts">
import { Vue, Options } from "vue-class-component";
import { useStore } from "../store";
import ReceiptPayement from "../service/ReceiptPayement";
import moment from "moment";
import { camelCase } from "lodash";
import Toaster from "../helpers/Toaster";


interface PaymentListType {
  paymentType: string;
  accountNo: string;
  terminalId: string;
  authCode: string;
  transId: string;
  transStatus: string;
  transType: string;
  transDate: string;
  transTime: string;
  transAmount: number;
  transTotalAmount: number;
  transRef: string;
  entryMode: string;
  hostResponse: string;
  giftCardRef: string;
  cardBalance: string;
  tendered: number;
  change: number;
  roundOff: number;
  transSource: string;
}


@Options({
  components: {},
})
export default class ReceiptPayment extends Vue {
  private store = useStore();
  private txnService;
  private toast;
  private receiptPayments: PaymentListType [] = [];


  created() {
    this.txnService = new ReceiptPayement();
    this.toast = new Toaster();
  }

  get receiptID() {
    return this.store.getters.getReceiptID;
  }

  mounted() {
    this.getReceiptPayments();
  }

  getReceiptPayments() {
    this.txnService.getPayments(this.receiptID).then((data) => {
      const receipt = this.camelizeKeys(data);
      this.receiptPayments = receipt.invoicePayment;
    });
  }

  formatDate(value) {
    if (value) {
      return moment(String(value)).format("DD-MM-YYYY");
    }
  }

  formatTime(value) {
    if (value) {
      const time = moment.duration(value);
      return moment(String(time), "HH:mm").format("hh:mm A");
    }
  }

  camelizeKeys = (obj) => {
    if (Array.isArray(obj)) {
      return obj.map((v) => this.camelizeKeys(v));
    } else if (obj !== null && obj.constructor === Object) {
      return Object.keys(obj).reduce(
        (result, key) => ({
          ...result,
          [camelCase(key)]: this.camelizeKeys(obj[key]),
        }),
        {}
      );
    }
    return obj;
  };

  fixLength(amount) {
    amount = Number(amount);

    if (amount != " ") {
      amount = amount.toFixed(2);
    }

    return amount;
  }

  totalAmount()
  {

    let totalBalance = 0;

     this.receiptPayments.forEach(e => {
         totalBalance = totalBalance + e.transTotalAmount;
     });

    return totalBalance.toFixed(2);
  }
}
</script>

<style scoped>
.border-btm {
  border-bottom: 1px solid #eee;
  margin-top: 5px;
  margin-bottom: 15px;
  display: block;
}

.history-set-height {
  height: 90.5vh;
  min-height: 90.5vh;
  overflow-y: scroll;
}

table tr td,
th {
 font-size: 18px;
  padding: 4px;
}

.payment-source
{
  float: right;
  text-transform: uppercase;
}
</style>
