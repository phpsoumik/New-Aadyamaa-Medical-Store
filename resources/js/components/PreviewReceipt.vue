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
          <span class="p-mr-2 p-text-uppercase ">  <b> TO : </b> {{items.selectedProfile}}</span>
          <span class="p-mx-2 p-text-uppercase ">  <b> VOUCHER NO : </b> {{items.voucherNo}} </span>
          <span class="p-mx-2 p-text-uppercase ">  <b> DATE : </b> {{formatDate(items.voucherDate)}} </span>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-col">
            <DataTable :value="items.itemList" class="p-datatable-sm p-datatable-gridlines">
                <Column style="width: 60%" class="p-p-1" field="accountName" header="ACCOUNT NAME"></Column>
                <Column style="width: 20%" class="p-p-1" header="DEBIT" v-if="showOnly == 'Both' || showOnly == 'Debit'">
                    <template #body="slotProps">
                    {{currency}}  {{
                        fixDigits(slotProps.data.debitAmount)
                      }}
                    </template>
                </Column>
                <Column style="width: 20%" class="p-p-1" field="creditAmount" header="CREDIT" v-if="showOnly == 'Both' || showOnly == 'Credit'">
                   <template #body="slotProps">
                     {{currency}} {{
                        fixDigits(slotProps.data.creditAmount)
                      }}
                    </template>
                </Column>
            </DataTable>
        </div>
      </div>
      <p>
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
import VoucherService from "../service/VoucherService.js";
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
export default class PreviewReceipt extends Vue {
  private toast;
  private store = useStore();
  private productDialog = false;
  private showOnly = "Both";
  private dialogTitle = "";
  private voucherService;
  private previewHeading = '';
  private items = {
    storeName: "",
    storeAddress: "",
    storeEmail: "",
    storePhone: "",
    storeLicense: "",
    voucherNo: "",
    type: "",
    description: "",
    voucherDate: "",
    selectedProfile: "",
    itemList: [
      {
        accountName: '',
        debitAmount: 0,
        creditAmount: 0
      }
    ]
  };

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.toast = new Toaster();
    this.voucherService = new VoucherService();
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
    this.voucherService.getReceiptData(receiptID).then((res) => {
      if (res != null) {
        this.items.storeName         = res.storeDetail.name;
        this.items.storeAddress      = res.storeDetail.address;
        this.items.storeEmail        = res.storeDetail.email;
        this.items.storePhone        = res.storeDetail.contact;
        this.items.storeLicense        = res.storeDetail.license_no;
        this.items.voucherNo         = res.voucher.voucher_no;
        this.items.description       = res.voucher.memo;
        this.items.voucherDate       = res.voucher.voucher_date;
        this.items.type              = res.voucher.type;
        this.items.selectedProfile   = res.voucher.profile_name;


        let vList = res.voucherList;
       
        if(vList.length > 0)
        {
          this.items.itemList = [];
          vList.map(v => {
            if(this.items.type == 'JRV' || this.items.type == 'OPB' )
            {
              this.showOnly = "Both";

              if(v.type == "Debit")
              {
                this.items.itemList.push({
                    accountName: v.account_name,
                    debitAmount: Number(v.amount),
                    creditAmount: 0,
                });
              }
              else
              {
                this.items.itemList.push({
                    accountName: v.account_name,
                    debitAmount: 0,
                    creditAmount: Number(v.amount),
                });
              }
            }
            else if(this.items.type == 'CRV')
            {
              this.showOnly = "Credit";

              if(v.type == "Credit")
              {
                this.items.itemList.push({
                    accountName: v.account_name,
                    debitAmount: 0,
                    creditAmount: Number(v.amount),
                });
              }
            }
            else if(this.items.type == 'DBV')
            {
              this.showOnly = "Debit";

              if(v.type == "Debit")
              {
                this.items.itemList.push({
                    accountName: v.account_name,
                    debitAmount: Number(v.amount),
                    creditAmount: 0,
                });
              }
            }
            else if(this.items.type == 'EXV')
            {
              this.showOnly = "Debit";

              if(v.type == "Debit")
              {
                this.items.itemList.push({
                    accountName: v.account_name,
                    debitAmount: Number(v.amount),
                    creditAmount: 0,
                });
              }
            }
        });

          this.items.itemList.push({
            accountName: 'TOTAL',
            debitAmount: Number(this.fixDigits(this.totalDebitAmount)),
            creditAmount: Number(this.fixDigits(this.totalCreditAmount)),
          });
        }
      }
    });
  }


  get totalDebitAmount() {
    let totalAmount = 0;
    this.items.itemList.forEach((v) => {
      totalAmount = totalAmount + Number(v.debitAmount);
    });
    return totalAmount;
  }

  get totalCreditAmount() {
    let totalAmount = 0;
    this.items.itemList.forEach((v) => {
      totalAmount = totalAmount + Number(v.creditAmount);
    });
    return totalAmount;
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