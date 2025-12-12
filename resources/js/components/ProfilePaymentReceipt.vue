<template>
  <Dialog
    id="previewReceiptDailog"
    v-model:visible="productDialog"
    :style="{ width: '60vw' }"
    position="top"
    class="p-fluid"
    :modal="true"
    :closable="true"
    @hide="closeDialog"
  >
    <template #header>
      <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-eye"></i> {{ dialogTitle }}
      </h5>
    </template>
    <h3
      class="p-mb-2 p-mt-1 p-text-bold p-text-uppercase"
      style="color: #004c97"
    >
      {{ previewHeading }}
    </h3>
    <div class="p-grid">
      <div class="p-col">
        <span class="p-mr-2 p-text-uppercase">
          <b> FROM : </b> {{ item.selectedProfile }}</span
        >
        <span class="p-mx-2 p-text-uppercase">
          <b> Receipt NO : </b> {{ item.receiptNO }}
        </span>
        <span class="p-mx-2 p-text-uppercase">
          <b> RECEIPT DATE : </b>
          {{ formatDate(item.receiptDate) }}
        </span>
        <span
          class="p-mx-2 p-text-uppercase"
          v-if="previewHeading == 'Invoice'"
        >
          <b> DUE DATE : </b>
          {{ formatDate(item.receiptDueDate) }}
        </span>
        <span class="p-mx-2 p-text-uppercase">
          <b> TOTAL BALANCE : </b>
          {{ formatAmount(calculateBalance) }}
        </span>
      </div>
    </div>
    <div class="p-grid">
      <div class="p-col-12">
        <div class="form-group">
          <label for="paymentMethod">Payment Method</label>
          <Dropdown
            id="paymentMethod"
            v-model="item.method"
            :options="methodList"
            optionLabel="cardName"
          />
        </div>
      </div>
      <div class="p-col-12">
        <div class="form-group">
          <label for="paymentMethod">Amount</label>
          <InputNumber
            placeholder="e.g 20"
            v-model="item.totalAmount"
            mode="decimal"
            :maxFractionDigits="2"
            :minFractionDigits="2"
          />
        </div>
      </div>
      <div class="p-col-12">
         <div class="p-field">
              <label for="description">Description</label>
              <InputText
                  id="description"
                  placeholder="Any description"
                  v-model="item.description"
              />
              
          </div>
      </div>
      <div class="p-col-12">
        <div class="form-group">
          <Button
            icon="pi pi-check-circle"
            :disabled="item.totalAmount > calculateBalance"
            label="Save"
            class="p-button-primary"
            @click="saveReceiptPayment"
          />
        </div>
      </div>
    </div>
  </Dialog>
</template>

<script lang="ts">
import { Options, mixins   } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import ReceiptService from "../service/ReceiptService.js";
import UtilityOptions from "../mixins/UtilityOptions";
import PaymentService from "../service/PaymentService";

interface IPaymentMethod {
  bankId: number;
  branchId: number;
  cardCharges: number;
  amount: number;
  cardName: string;
  chargeCustomer: string;
  id: string;
}

interface IReceiptBalance {
  transTotalAmount: number;
}


@Options({
  props: {
    ProfilePaymentReceipt: Object,
  },
  watch: {
    ProfilePaymentReceipt(obj) {
      this.openDialog();
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
      this.previewHeading = obj.previewHeading;

      if (obj.receiptID != 0) {
        this.loadReceipt(obj.receiptID);
      }
    },
  },
  emits: ["updatePaymentStatus"],
})
export default class ProfilePaymentReceipt extends mixins(UtilityOptions) {
  private toast;
  private productDialog = false;
  private methodList: IPaymentMethod [] = [];
  private receiptBalance: IReceiptBalance [] = [];
  private dialogTitle = "";
  private receiptService;
  private paymentService;
  private totalBill = 0;
  private previewHeading = "";
  private item = {
    id: "",
    receiptNO: "",
    type: "",
    profileId: "",
    receiptDate: "",
    receiptDueDate: "",
    selectedProfile: "",
    method: {
      cardCharges:0,
      chargeCustomer:'',
      cardName:'Cash',
      bankId:0,
      branchId:0,
      amount: 0,
      id:''
    },
    totalAmount: 0,
    description: ""
  };

  private counterEntry = [
    {
      accountID: 0,
      accountHead: "",
      amount: 0,
      type: "Debit",
    },
  ];


  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.toast = new Toaster();
    this.receiptService = new ReceiptService();
    this.paymentService = new PaymentService();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.productDialog = true;
  }

  closeDialog() {
    this.$emit("updatePaymentStatus", {});
    this.productDialog = false;
  }

  loadReceipt(receiptID) {
    this.receiptService.getReceiptData(receiptID).then((res) => {
      if (res != null) {
        
        this.item.profileId     = res.receipt.profile_id;
        this.item.receiptDate     = res.receipt.receipt_date;
        this.item.receiptDueDate  = res.receipt.receipt_due_date;
        this.item.type            = res.receipt.type;
        this.item.id              = res.receipt.id;
        this.item.receiptNO       = res.receipt.receipt_no;
        this.item.selectedProfile = res.receipt.profile_name.profileName;
        this.totalBill            = Number(res.receipt.total_bill);
        this.receiptBalance       = this.camelizeKeys(res.receipt.receipt_balance);
      }
    });

    this.paymentService.paymentMethods().then((res) => {
      this.methodList = this.camelizeKeys(res.option);

      this.methodList.push({
        cardCharges:0,
        chargeCustomer:'',
        cardName:'Cash',
        bankId:0,
        branchId:0,
        amount: 0,
        id:''
      });
    });
  }

 
  get calculateBalance() {
    let totalAmount = 0;

    this.receiptBalance.forEach((e) => {
      totalAmount = totalAmount + Number(e.transTotalAmount);
    });

    return Number((this.totalBill - totalAmount).toFixed(2));
  }

  saveReceiptPayment()
  {
    this.setAccountingEntries();
    this.receiptService.saveReceiptPayment(this.item,this.counterEntry).then((res) => {
      this.toast.handleResponse(res);
      this.closeDialog();
    });
  }

  setAccountingEntries() {
    
    this.counterEntry = [];

    let cashOrBankHeadID = 0;
    let cashOrBankHeadName = "";

    if (this.item.method.cardName == "Cash") {
      cashOrBankHeadID = 2;
      cashOrBankHeadName = "Cash in hand";
    } else {
      cashOrBankHeadID = 8;
      cashOrBankHeadName = "Cash at bank";
    }

    if (this.item.type == "SLS" || this.item.type == "INV") {
      this.counterEntry.push({
        accountID: cashOrBankHeadID,
        accountHead: cashOrBankHeadName,
        amount: this.item.totalAmount,
        type: "Debit",
      });

      this.counterEntry.push({
        accountID: 4,
        accountHead: 'Accounts receivable',
         amount: this.item.totalAmount,
        type: "Credit",
      });
    } 
    else if(this.item.type == "RFR") {
       this.counterEntry.push({
        accountID: 3,
        accountHead: 'Accounts payable',
        amount: this.item.totalAmount,
        type: "Debit",
      });

      this.counterEntry.push({
        accountID: cashOrBankHeadID,
        accountHead: cashOrBankHeadName,
         amount: this.item.totalAmount,
        type: "Credit",
      });
    }
    else {
        //
    }
  }
}
</script>