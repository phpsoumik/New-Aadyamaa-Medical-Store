<template>
  <Dialog
    id="previewReceiptDailog"
    v-model:visible="productDialog"
    :style="{ width: '100vw' }"
    position="top"
    class="p-fluid p-m-0 p-dialog-maximized"
    :modal="true"
    :closable="true"
    @hide="closeDialog"
  >
    <template #header>
      <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-eye"></i> {{ dialogTitle }}
      </h5>
    </template>
     <div class="p-d-flex p-jc-between">
        <div>
            <h3>{{items.storeName}}</h3>
            <h6>{{items.storeAddress}}</h6>
            <h6>{{items.storeEmail}}</h6>
            <h6>{{items.storePhone}}</h6>
            <h6>License No : {{items.storeLicense}}</h6>
        </div>
        <div>
            <img  class="company-logo" :src="getCompanyURL()" alt="Company Logo" />
        </div>
      </div>  
      <h3 class="p-mb-2 p-mt-1 p-text-bold p-text-uppercase" style="color:#004C97">{{previewHeading}}</h3> 
      <div class="p-grid">
        <div class="p-col"> 
          <span v-if="items.type != 'DPT'" class="p-mr-2 p-text-uppercase ">  <b> TO : </b> {{items.selectedProfile}}</span>
          <span class="p-mx-2 p-text-uppercase ">  <b> RECEIPT NO : </b> {{items.receiptNo}} </span>
          <span class="p-mx-2 p-text-uppercase ">  <b> DATE : </b> {{formatDate(items.receiptDate)}} </span>
          <span class="p-mx-2 p-text-uppercase ">  <b> BANK NAME : </b> {{items.bankName}} ({{items.bankAccount}}) </span>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-col">
            <DataTable :value="items.itemList"  class="p-datatable-sm p-datatable-gridlines">
                <Column style="width: 80%" class="p-p-1" field="accountName" header="ACCOUNT NAME"></Column>
                <Column style="width: 20%" class="p-p-1" header="AMOUNT">
                   <template #body="slotProps">
                     {{currency}}  {{
                        fixDigits(slotProps.data.amount)
                      }}
                    </template>
                </Column>
            </DataTable>
        </div>
      </div>
      <p class="p-m-0">
         <b> TRANSACTION NO : </b> {{items.transactionNo}}
      </p>
      <p class="p-m-0">
        Description : {{items.description}}
      </p>
      <div class="p-text-center">
        <p>
            Authorized Signatory ___________________ Accountant ___________________
        </p>
      </div>
      <template #footer>
        <Button
          type="button"
          label="Print"
          icon="pi pi-print"
          class="p-button-warning pull-left"
          @click="printReceipt()"
        />
        </template>
  </Dialog>
</template>

<script lang="ts">
import moment from "moment";
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import BankingService from "../service/BankingService.js";
import { useStore, ActionTypes } from "../store";

@Options({
  props: {
    PreviewReceipt: Object,
  },
  watch: {
    PreviewReceipt(obj) {
      
      this.openDialog();
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
      this.previewHeading = obj.previewHeading;

      if(obj.receiptID != 0)
      {
        this.loadReceipt(obj.receiptID);
      }
    },
  },
  emits: ["updatePreviewStatus"],
})
export default class PreviewBankingReceipt extends Vue {
  private toast;
  private productDialog = false;
  private store = useStore();
  private dialogTitle = "";
  private bankingService;
  private previewHeading = '';
  private items = {
    storeName: "",
    storeAddress: "",
    storeEmail: "",
    storePhone: "",
    storeLicense: "",
    receiptNo: "",
    transactionNo: "",
    type: "",
    description: "",
    receiptDate: "",
    bankName: "",
    bankAccount: "",
    selectedProfile: "",
    itemList: [{
      accountName: '',
      amount: 0,
    }]
  };

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.toast = new Toaster();
    this.bankingService = new BankingService();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.productDialog = true;
  }

  closeDialog() {
    this.$emit("updatePreviewStatus", {});
    this.productDialog = false;
  }

  getCompanyURL() 
  {
    return  require('@/assets/images/logo.png').default;
  }

  loadReceipt(receiptID)
  {
    this.bankingService.getReceiptData(receiptID).then((res) => {
      if (res != null) {
        this.items.storeName         = res.storeDetail.name;
        this.items.storeAddress      = res.storeDetail.address;
        this.items.storeEmail        = res.storeDetail.email;
        this.items.storePhone        = res.storeDetail.contact;
        this.items.storeLicense      = res.storeDetail.license_no;
        this.items.receiptNo         = res.receipt.receipt_no;
        this.items.transactionNo     = res.receipt.transaction_no;
        this.items.type              = res.receipt.type;
        this.items.description       = res.receipt.description;
        this.items.receiptDate       = res.receipt.receipt_date;
        this.items.bankName          = res.receipt.bank_name.bankName;
        this.items.bankAccount       = res.receipt.bank_name.bankAccountNo;
        this.items.selectedProfile   = res.receipt.profile_name.profileName;
        
        this.items.itemList = [];
        let account = {accountName: res.receipt.account_head,amount: res.receipt.amount}
        this.items.itemList.push(account);
      }
    });
  }

  fixDigits(amt) {
    return Number(amt).toFixed(2);
  }

  formatDate(date)
  {
    return moment(date).format("DD-MM-YYYY");
  }

  printReceipt() {
    window.print();
	}

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.company-logo
{
  width: 120px;
  height: auto;
}
</style>