<template>
  <Dialog
    v-model:visible="paymentDialog"
    :style="{ width: '100vw' }"
    :modal="true"
    :closable="true"
    position="top"
    @hide="confirmPaymentCancel"
    class="p-fluid p-m-0 p-dialog-maximized"
    :showHeader="true"
  >
    <template #header>
      <h6 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-money-bill py-icon-color"></i> {{ dialogTilte }}
      </h6>
    </template>
    <!--row open-->
    <div class="row text-center">
      <div class="col-md-12">
        <p class="pay-size-bx">
          <label class="py-span">
            <img src="@/assets/pay/cash.png" />
            Cash
            <RadioButton v-model="paymentMethodType" value="Cash" />
          </label>
        </p>
        <p class="pay-size-bx">
          <label class="py-span">
            <img src="@/assets/pay/upi-icon.png" />
            UPI
            <RadioButton v-model="paymentMethodType" value="UPI" />
          </label>
        </p>
        <p class="pay-size-bx">
          <label class="py-span">
            <img src="@/assets/pay/manual.png" />
            Card
            <RadioButton v-model="paymentMethodType" value="Manual" />
          </label>
        </p>
        <p class="pay-size-bx" v-if="restriction != 'Yes'">
          <label class="py-span">
            <img src="@/assets/pay/paylater.png" />
            Pay Later
            <RadioButton v-model="paymentMethodType" value="Pay Later" />
          </label>
        </p>
      </div>
    </div>
    <div class="row py-description">
      <div style="height: 0.2em; background-color: #fff" class="col-md-12">
        <ProgressBar
          v-if="progressBar"
          mode="indeterminate"
          style="height: 0.2em"
        />
      </div>
      <div class="col-md-2 col-sm-12 content-height p-pl-1 p-pr-0">
        <h5 v-if="advancePayment > 0" style="background-color: #fff3cd; padding: 8px; border-radius: 4px;">
          <span style="color: #ff9800;">💰 Advance Paid</span>
          <input
            style="color: #ff9800; font-weight: bold;"
            type="text"
            :value="fixLength(advancePayment)"
            readonly
            class="form-control py-customize-bx mt-1"
          />
        </h5>
        <h5 :class="{'mt-2': advancePayment > 0}">
          ({{currency}}) Amount Due
          <input
          style="color: black"

            type="text"
            :value="fixLength(totalBill - advancePayment)"
            readonly
            class="form-control py-customize-bx mt-1 py-balance-due"
          />
        </h5>
        <h5 class="mt-2">
          ({{currency}}) Tendered
          <input
            style="color: green"
            type="number"
            readonly
            :value="fixLength(paymentAction.tendered)"
            class="form-control py-customize-bx mt-1"
          />
        </h5>
        <h5 class="mt-2">
          ({{currency}}) Change
          <input
           style="color: red"

            type="number"
            readonly
            :value="fixLength(changeAmount)"
            class="form-control py-customize-bx mt-1"
          />
        </h5>
        <h5 class="mt-2">
          ({{currency}}) Round Off
          <input
            type="number"
            readonly
            :value="fixLength(roundedAmt)"
            class="form-control py-customize-bx mt-1"
          />
        </h5>
      </div>
      <div class="col-md-3 col-sm-12">
        <div class="content-height">
          <h5>
            <i class="pi pi-file-o py-icon-color" aria-hidden="true"></i>
            Amount Paid
          </h5>
          <!-- keypad -->
          <div
            class="btn-group-vertical text-center"
            role="group"
            aria-label="Basic example"
          >

          <!--sam-->
          <InputNumber
                inputid="integeronly"
                id="paidamount"
                style="height: 60px;width: 10rem;"
                v-model="paymentAction.tendered"
                class="p-p-1"
                autofocus
                @keyup.enter="addCashAmount()"


            />
            <!-- @keyup.enter="addCashAmount()" -->
            <!-- <div class="btn-group btn-group-lg">
              <button
                type="button"
                @click="amountNumpad(1)"
                class="btn-numpad col-sm-4"
              >
                1
              </button>
              <button
                type="button"
                @click="amountNumpad(2)"
                class="btn-numpad col-sm-4"
              >
                2
              </button>
              <button
                type="button"
                @click="amountNumpad(3)"
                class="btn-numpad col-sm-4"
              >
                3
              </button>
            </div>
            <div class="btn-group btn-group-lg">
              <button type="button" @click="amountNumpad(4)" class="btn-numpad">
                4
              </button>
              <button type="button" @click="amountNumpad(5)" class="btn-numpad">
                5
              </button>
              <button type="button" @click="amountNumpad(6)" class="btn-numpad">
                6
              </button>
            </div>
            <div class="btn-group btn-group-lg">
              <button type="button" @click="amountNumpad(7)" class="btn-numpad">
                7
              </button>
              <button type="button" @click="amountNumpad(8)" class="btn-numpad">
                8
              </button>
              <button type="button" @click="amountNumpad(9)" class="btn-numpad">
                9
              </button>
            </div>
            <div class="btn-group btn-group-lg">
              <button
                type="button"
                @click="amountNumpad('.')"
                class="btn-numpad"
              >
                .
              </button>
              <button type="button" @click="amountNumpad(0)" class="btn-numpad">
                0
              </button>
              <button
                type="button"
                @click="amountNumpad('del')"
                class="btn-numpad-danger"
              >
                Del
              </button>
            </div> -->
            <!-- <div class="btn-group btn-group-lg" style="margin-top: 1px">
              <Button
                label="Exact"
                icon="pi pi-tick"
                class="p-button-lg p-button-primary p-4"
                @click="exactAmount()"
                :disabled="paymentMethodType == ''"
              />
            </div> -->
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-12">
        <div v-if="paymentMethodType == 'Manual'" class="content-height">
          <h5>
            <i class="pi pi-id-card py-icon-color" aria-hidden="true"></i>
            Add Card Payments Manually
          </h5>
          <div class="transactions-card-manual">
            <div class="form-group">
              <label> Card Type</label>
              <Dropdown
                :options="methodList"
                v-model="cardType"
                optionLabel="cardName"
              />  
            </div>
            <div class="form-group">
              <label> Account No (Last 4 digits)</label>
              <InputText
                placeholder="e.g 3217"
                v-model.trim="accountNo"
              />
            </div>
          </div>
          <Button
            label="Add Card Payment"
            icon="pi pi-money-bill"
            @click="addManualAmount"
            class="p-button-lg p-button-warning p-4"
          />
        </div>
        <div v-if="paymentMethodType == 'Cash'" class="content-height">
          <div class="transactions">
            <!-- <h5>
              <i class="pi pi-money-bill py-icon-color" aria-hidden="true"></i>
              Add Tendered Cash
            </h5> -->
          </div>
          <div class="col-md-12 p-0">
            <!-- <Button
              label="Add Cash"
              icon="pi pi-money-bill"
              class="p-button-lg p-button-warning p-4"
              @click="addCashAmount()"
              :disabled="paymentMethodType == ''"
            /> -->
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-12">
        <div class="content-height">
          <div class="transactions">
            <h5 class="">
              <i class="pi pi-money-bill py-icon-color" aria-hidden="true"></i>
              Payment Methods
            </h5>
            <table
              id="customer_payment_method_manual"
              class="
                table table-bordered table-striped
                py-list-collections
                table-hover
              "
            >
              <tr v-for="(item, index) in paymentList" :key="item">
                <td class="text-left">{{ item.paymentType }}</td>
                <td class="text-left">
                  {{currency}} <span id="history_total_cash">{{
                    fixLength(item.tendered)
                  }}</span>
                  <span
                    @click="deletePayment(index, item)"
                    class="payment_cross"
                    ><i class="pi pi-times"></i
                  ></span>
                </td>
              </tr>
              <tr>
                <td style="background-color: green; color: #fff">
                  Total
                </td>
                <td
                  class="text-left"
                  style="background-color: green; color: #fff"
                >
                  {{currency}} <b>{{ fixLength(totalPaymentsReceived) }}</b>
                </td>
              </tr>
            </table>
          </div>
          <Button
            label="Done"
            id="cnf"
            icon="pi pi-check-circle"
            class="p-button-lg p-button-primary p-4"
            @keyup.enter="confirmPayments()"
            
          />
          <!-- @click="confirmPayments()" -->
          <!-- @keyup.enter="confirmPayments()" -->

          <!--SAM 
          <Button
                            icon="pi pi-print"
                            label="PRINT RECEIPT"
                            @click="this.posreceipt.previewDialog()"
                            class="p-button-primary p-button-md p-mx-2"
            />-->
        </div>
      </div>
    </div>
  </Dialog>
  <Dialog
    v-model:visible="paymentCancelDialog"
    :style="{ width: '600px' }"
    header="Confirm"
  >
    <div class="confirmation-content">
      <i class="pi pi-exclamation-triangle p-mr-3" style="font-size: 2rem" />
      <span
        >Are you sure to cancel ? You will lost the payments of amount
        <b> ${{ fixLength(totalPaymentsReceived) }}</b>
      </span>
    </div>
    <template #footer>
      <Button
        label="No"
        icon="pi pi-times"
        class="p-button-text"
        @click="cancelPaymentConfirm"
      />
      <Button
        label="Yes"
        icon="pi pi-check"
        class="p-button-danger"
        @click="closePaymentScreen"
      />
    </template>
  </Dialog>
  <Dialog
    v-model:visible="paymentConfirmDialog"
    :style="{ width: '600px' }"
    header="Confirm"
  >
    <div class="confirmation-content">
      <i class="pi pi-exclamation-triangle p-mr-3" style="font-size: 2rem" />
      <span
        >Are you sure to continue this invoice. The remaining balance for this
        invoice is <b> ${{ fixLength(paymentRounding) }}</b> ?
      </span>
    </div>
    <template #footer>
      <Button
        label="No"
        icon="pi pi-times"
        class="p-button-text"
        @click="paymentConfirmDialog = false"
      />
      <Button
        label="Yes"
        icon="pi pi-check"
        class="p-button-danger"
        @click="emitPayments()"
      />
    </template>
  </Dialog>
</template>

<script lang="ts">
import {  Options,mixins } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import PaymentService from "../service/PaymentService";
import UtilityOptions from "../mixins/UtilityOptions";
// import PosPreviewReceipt from "../../components/PosPreviewReceipt.vue";
 import TransactionReceipt from "pages/transaction/TransactionReceipt.vue";
 import InputNumber from 'primevue/inputnumber';


interface IPaymentMethod {
  bankId: number;
  branchId: number;
  cardCharges: number;
  cardName: string;
  chargeCustomer: string;
  id: string;
}

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
   bankID: number;
}

@Options({
  props: {
    receiptDetail: Object,
  },
  watch: {
    receiptDetail(obj) {
      this.paymentDialog = obj.dialogStatus;
      this.closeConfirmation = obj.closeConfirmation;
      this.amountLeft = Number(this.totalBill);
      this.itemSource = obj.itemSource;
      this.restriction = obj.restriction;
      this.customerID = obj.customerID;
      this.customerName = obj.customerName;
      this.paymentAction.needlePoints = obj.needlePoints;
      this.advancePayment = Number(obj.advancePayment) || 0;
      this.dialogTilte = obj.dialogTilte + " for Customer " + this.customerName;
    },
  },
  emits: ["closePaymentScreenEvent", "getProceededPaymentsEvent"],
})

export default class PaymentScreen extends mixins(UtilityOptions) {
  
  private customerID;
  private methodList: IPaymentMethod [] = [];
  private customerName;
  private accountNo = "";
  private advancePayment = 0;
  private cardType = {
    bankId: 0,
    branchId: 0,
    cardCharges: 0,
    cardName: '',
    chargeCustomer: '',
    id:0
  }
  //sam
  private posreceipt;
  private paymentService;
  private paymentDialog = false;
  private paymentConfirmDialog = false;
  private closeConfirmation = false;
  private paymentCancelDialog = false;
  private itemSource = "";
  private transStatus = "000";
  private toast;
  private restriction = "";
  private screenNumber = "";
  private paymentMethodType = "Cash";
  private amountLeft = 0;
  private roundedAmt = 0;
  private tipAmountTerminal = 0;
  private paymentAction = {
  tendered: 0,
  needlePoints: 0,
  };

  private paymentList: PaymentListType[] = [];

  created() {
    this.paymentService = new PaymentService();
    //this.posreceipt= new TransactionReceipt();
    
    this.toast = new Toaster();
  }

  mounted()
  {
      this.loadPaymentMethod();
  }

  get progressBar() {
    return this.store.getters.getProgressBar;
  }

  get totalBill() {
    return this.store.getters.getTotalBill;
  }

  closePaymentScreen() {
    this.paymentList = [];
    this.$emit("closePaymentScreenEvent");
    this.paymentCancelDialog = false;
  }

  cancelPaymentConfirm() {
    this.paymentDialog = true;
    this.paymentCancelDialog = false;
  }

  confirmPaymentCancel() {
    if (this.totalPaymentsReceived > 0) {
      this.paymentCancelDialog = true;
    } else {
      this.paymentList = [];
      this.$emit("closePaymentScreenEvent");
      this.paymentDialog = false;
    }
  }

  amountNumpad(num) {
    num = String(num);

    if (num == "del") {
      this.paymentAction.tendered = 0;
      this.screenNumber = "";
    } else {
      if (this.paymentRounding > 0 || this.paymentMethodType == "Tip") {
        this.screenNumber = this.screenNumber + num;
        this.paymentAction.tendered = Number(this.screenNumber);
      } else {
        this.toast.showWarning("Invalid Amount must be greater then zero");
      }
    }
  }

  exactAmount() {
    this.paymentAction.tendered = this.paymentRounding;
  }

  fixLength(value) {
    const num = Number(value);
    value = num.toFixed(2);
    return value;
  }

  fixLengthNumber(value) {
    const num = Number(value);
    value = num.toFixed(2);
    value = Number(value);
    return value;
  }

  get totalPaymentsReceived() {
    let total = 0;

    this.paymentList.forEach((e) => {
      if (e.paymentType != "Tip") {
        total = total + e.transTotalAmount;
      }
    });

    return Number(total);
  }

  addCashAmount() {
    const tendered = Number(this.paymentAction.tendered);

    //alert("tendered amount in tf : "+this.paymentAction.tendered);
    if (tendered == 0) {
      this.toast.showError("Please enter amount greater then zero");
    } else {
      if (!this.checkCashPayment) {
        // const receivableAmount = this.fixLengthNumber(
        //   tendered - this.changeAmount
        // );
        this.paymentList.push({
          paymentType: "Cash",
          accountNo: "",
          transTotalAmount: tendered,
          terminalId: "Manual",
          authCode: "",
          hostResponse: "",
          transId: "",
          transStatus: this.transStatus,
          transType: this.itemSource,
          transDate: "",
          transTime: "",
          transAmount: tendered,
          transRef: "",
          entryMode: "",
          giftCardRef: "",
          cardBalance: "",
          tendered: this.fixLengthNumber(tendered),
          change: this.fixLengthNumber(this.changeAmount),
          roundOff: this.fixLengthNumber(this.roundedAmt),
          bankID: 0,
        });

        //console.log("focus - unfocus"+this.$refs.paidamount);
       //this.$refs.paidamount[0].blur();
       document.getElementById('paidamount').blur();
       //alert(document.getElementById('cnf'));
        document.getElementById('cnf').focus();
        //console.log(document.activeElement);
        //alert(document.activeElement);
      } else {
        this.toast.showError("Cash type is already added");
      }
    }
    
  }

  addManualAmount() {
    if (this.accountNo.length != 4 || this.cardType == null) {
      this.toast.showError("Please choose Card Type and Card No must be 4 digits");
    } else {
      const tendered = Number(this.paymentAction.tendered);
      if (tendered == 0) {
        this.toast.showError("Please enter amount greater then zero");
      } else {
        const receivableAmount = this.fixLengthNumber(
          tendered - this.changeAmount
        );

        this.paymentList.push({
          paymentType: this.cardType.cardName,
          accountNo: this.accountNo,
          transTotalAmount: receivableAmount,
          terminalId: "Manual",
          authCode: "",
          hostResponse: "",
          transId: "",
          transStatus: this.transStatus,
          transType: this.itemSource,
          transDate: "",
          transTime: "",
          transAmount: receivableAmount,
          transRef: "",
          entryMode: "",
          giftCardRef: "",
          cardBalance: "",
          bankID: this.cardType.bankId,
          tendered: this.fixLengthNumber(tendered),
          change: this.fixLengthNumber(this.changeAmount),
          roundOff: 0,
        });
        this.toast.showSuccess(this.cardType.cardName + " Payment added successfully");
        this.accountNo = "";
      }
    }
  }

  get changeAmount() {
    let change = 0;
    const amountLeft = this.paymentRounding;
    const tendered = Number(this.paymentAction.tendered);
    const amountDue = Number(this.totalBill) - this.advancePayment;
    //alert("in change amount "+this.amountLeft+"..."+this.paymentRounding+".."+this.paymentAction.tendered);
   // const balance = tendered - amountLeft;
    const balance = tendered - amountDue;

    if (balance > 0) {
      change = balance;
    }

    return change;
  }

  deletePayment(index, obj) {
    this.paymentList.splice(index, 1);
    this.toast.showSuccess(
      "Amount of $" +
        this.fixLength(obj.transTotalAmount) +
        " removed successfully"
    );
  }


  clearAccountNo() {
    this.accountNo = "";
  }

  get checkCashPayment() {
    let status = false;
    this.paymentList.forEach((e) => {
      if (e.paymentType == "Cash") {
        status = true;
      }
    });

    return status;
  }


  get paymentRounding() {
    let amountLeftTemp = 0;
    let amountPaidTemp = 0;

    //REDUCING THE AMOUNT PAID
    this.paymentList.forEach((e) => {
      if (e.paymentType != "Tip") {
        amountPaidTemp = amountPaidTemp + e.transTotalAmount;
      }
    });

    if (this.paymentMethodType == "Cash") {
      const amountLeft = this.amountLeft - amountPaidTemp;
      const roundNum = Math.round(amountLeft / 0.05) * 0.05;
      amountLeftTemp = Number(roundNum);

      if (this.roundedAmt == 0) {
        this.roundedAmt = roundNum - amountLeft;
      }
    } else if (this.paymentMethodType != "Tip") {
      amountLeftTemp = this.amountLeft - amountPaidTemp;
      this.roundedAmt = 0;
    } else {
      //nothing
    }

    //this.paymentAction.tendered = 0;
    this.screenNumber = "";

    return amountLeftTemp;
  }

  confirmPayments() {
    const amountDue = this.totalBill - this.advancePayment;
    if(this.paymentAction.tendered >= amountDue){
       this.emitPayments();

    }else{
      this.toast.showError("Paid amount is not enough. Amount Due: " + this.fixLength(amountDue));

    }
    //alert('confirm payment '+this.paymentRounding);
    //this.restriction == 'Yes'
    // if (this.paymentRounding > 0.2 && this.restriction == "No") {
    //   this.paymentConfirmDialog = true;
    // } else {
    //  this.emitPayments();
    // }
  }

  emitPayments() {
   // alert("emit payments : "+JSON.stringify(this.paymentList));
    this.$emit("getProceededPaymentsEvent", this.paymentList);
    this.paymentDialog = false;
    this.paymentConfirmDialog = false;
    this.paymentList = [];
    this.clearPaymentScreen();
   

  }

  clearPaymentScreen() {
    this.amountLeft = 0;
    this.paymentAction.tendered = 0;
    this.paymentAction.needlePoints = 0;

    //sam
    //this.closePaymentScreen();
  }

  loadPaymentMethod()
  {
    this.paymentService.paymentMethods().then((res) => {
      this.methodList = this.camelizeKeys(res.option);
    });
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.py-icon-color {
  color: orangered;
}

.pay-size-bx {
  background-color: #fff;
  border: 1px solid #eee;
  margin-right: 5px;
  display: inline-block;
}

.py-span {
  font-size: 16px;
  padding: 5px;
  width: 100%;
  border-radius: 5px;
  color: #000;
  background-color: #f7f7f7;
  margin: 0px;
  text-align: center;
  box-shadow: 0px 0px 5px 2px #ccc;
}

.py-span img {
  border-radius: 5px;
  width: 100%;
  display: block;
}

.py-description {
  border: 1px dotted #ccc;
  box-shadow: 0px 0px 10px 2px #eee;
  border-radius: 5px;
  padding: 2px 2px;
  margin: 1px;
}

.btn-numpad {
  width: 7.5vw;
  height: 10.8vh;
  background-color: #004c97;
  border-radius: 5px;
  font-size: 25px;
  color: #fff;
  border: 1px solid #fff;
}

.btn-numpad-danger {
  width: 7.5vw;
  height: 10.8vh;
  background-color: #c00;
  border-radius: 5px;
  font-size: 25px;
  border: 1px solid #fff;
  color: #fff;
}

.transactions {
  background-color: #fff;
  height: 48vh;
  min-height: 48vh;
  overflow-y: scroll;
}

.transactions-card-manual {
  background-color: #fff;
  height: 43vh;
  min-height: 43vh;
  overflow-y: scroll;
}

.transactions-card-manual td {
  padding: 2px;
}

.transactions td {
  padding: 2px;
}

.payment_cross {
  float: right;
  color: #c00;
}
.payment_cross:hover {
  cursor: pointer;
}

.content-height {
  margin: 8px 0px 15px 0px;
}

.py-customize-bx {
  height: 60px;
  background: transparent;
  margin: 0px;
  padding: 0px;
  font-size: 45px;
  border: none;
}

.py-balance-due {
  color: #c00;
}
</style>