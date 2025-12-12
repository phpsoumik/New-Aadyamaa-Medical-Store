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
        <h3>{{ item.storeName }}</h3>
        <h6>Address: {{ item.storeAddress }}</h6>
        <h6>Email: {{ item.storeEmail }}</h6>
        <h6>Ph:{{ item.storePhone }}</h6>
        <h6>License No : {{ item.storeLicense }}</h6>
      </div>
      <div>
        <img class="company-logo" :src="getCompanyURL()" alt="Company Logo" />
      </div>
    </div>
    <h3
      class="p-mb-2 p-mt-1 p-text-bold p-text-uppercase"
      style="color: #004c97"
    >
      {{ previewHeading }}
    </h3>
    <div class="p-grid">
      <div class="p-col">
        <span class="p-mr-2 p-text-uppercase">
          <b> TO : </b> {{ item.selectedProfile }}</span
        >
        <span class="p-mx-2 p-text-uppercase">
          <b> Receipt NO : </b> {{ item.receiptNO }}
        </span>
        <span class="p-mx-2 p-text-uppercase">
          <b> DATE : </b>
          {{ formatDate(item.receiptDate) }}
        </span>
        <span
          class="p-mx-2 p-text-uppercase"
          v-if="previewHeading == 'Invoice'"
        >
          <b> DUE DATE : </b>
          {{ formatDate(item.receiptDueDate) }}
        </span>
      </div>
    </div>
    <div class="p-grid">
      <div class="p-col">
        <DataTable
          :value="item.itemList"
          class="p-datatable-sm p-datatable-gridlines"
        >
          <Column
            style="width: 30%"
            class="p-p-1"
            field="accountHead"
            header="ACCOUNT NAME"
          ></Column>
          <Column
            style="width: 10%"
            class="p-p-1"
            header="QTY"
            field="quantity"
          >
          </Column>
          <Column style="width: 10%" class="p-p-1" header="PRICE">
            <template #body="slotProps">
              {{currency}} {{ formatAmount(slotProps.data.price) }}
            </template>
          </Column>
          <Column style="width: 10%" class="p-p-1" header="DISC(%)">
            <template #body="slotProps">
              {{ formatAmount(slotProps.data.discount) }}
            </template>
          </Column>
          <Column
            class="p-p-1"
            :header="taxNames[0].taxName + '(%)'"
            style="width: 10%"
            v-if="taxNames[0].show == 'true'"
          >
            <template #body="slotProps">
              {{currency}} {{ formatAmount(slotProps.data.tax1Value) }}
            </template>
          </Column>
          <Column
            class="p-p-1"
            :header="taxNames[1].taxName + '(%)'"
            style="width: 10%"
            v-if="taxNames[1].show == 'true'"
          >
            <template #body="slotProps">
               {{currency}} {{ formatAmount(slotProps.data.tax2Value) }}
            </template>
          </Column>
          <Column
            class="p-p-1"
            :header="taxNames[2].taxName + '(%)'"
            style="width: 10%"
            v-if="taxNames[2].show == 'true'"
          >
            <template #body="slotProps">
               {{currency}} {{ formatAmount(slotProps.data.tax3Value) }}
            </template>
          </Column>
          <Column header="SUBTOTAL" style="width: 10%">
            <template #body="slotProps">
               {{currency}} {{ formatAmount(slotProps.data.subTotal) }}
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
    <p>Description : {{ item.description }}</p>
    <div class="p-grid">
      <div class="p-col-12">
        <table class="table table-bordered total-lables">
          <tr>
            <td>Total Gross :  {{currency}} {{ formatAmount(item.totalGross) }}</td>
            <td>Total Disc :  {{currency}} {{ formatAmount(item.totalDiscount) }}</td>
            <td v-if="taxNames[0].show == 'true'">
              Total {{ taxNames[0].taxName }} :  {{currency}} {{ formatAmount(item.totalTax1) }}
            </td>
            <td v-if="taxNames[1].show == 'true'">
              Total {{ taxNames[1].taxName }} :   {{currency}} {{ formatAmount(item.totalTax2) }}
            </td>
            <td v-if="taxNames[2].show == 'true'">
              Total {{ taxNames[2].taxName }} :  {{currency}} {{ formatAmount(item.totalTax3) }}
            </td>
            <td>Total Tax :  {{currency}} {{ formatAmount(item.totalTax) }}</td>
            <td>Net Total :  {{currency}} {{ formatAmount(item.totalBill) }}</td>
            <td>Balance :  {{currency}} {{ formatAmount(totalBalance) }}</td>
          </tr>
        </table>
      </div>
      <div class="p-col-12">
        <h5>Receipt Payments</h5>
        <DataTable
          :value="PaymentLists"
          :scrollable="true"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <template #empty>
            <div class="p-text-center p-p-3">No records found</div>
          </template>
          <Column header="Created Date">
            <template #body="slotProps">
              {{ formatDateTime(slotProps.data.createdDate) }}
            </template>
          </Column>
          <Column field="sourceType" header="Type"></Column>
          <Column header="Receipt No">
            <template #body="slotProps">
              {{ slotProps.data.receiptNo }}
            </template>
          </Column>
          <Column field="paymentType" header="Payment Type"></Column>
          <Column header="Total Amount">
            <template #body="slotProps">
              {{currency}} {{ formatAmount(slotProps.data.transTotalAmount) }}
            </template>
          </Column>
          <Column field="description" header="Description"></Column>
        </DataTable>
      </div>
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
import { Options, mixins } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import ReceiptService from "../service/ReceiptService.js";
import UtilityOptions from "../mixins/UtilityOptions";

@Options({
  props: {
    PreviewAccountingReceipt: Object,
  },
  watch: {
    PreviewAccountingReceipt(obj) {
      this.openDialog();
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
      this.previewHeading = obj.previewHeading;

      if (obj.receiptID != 0) {
        this.loadReceipt(obj.receiptID);
      }
    },
  },
  emits: ["updatePreviewStatus"],
})
export default class PreviewAccountingReceipt extends mixins(UtilityOptions) {
  private toast;
  private totalBalance = 0;
  private PaymentLists = [
    {
      createdDate: "",
      receiptNo: "",
      transTotalAmount: 0,
      description: "",
      paymentType: "",
      sourceType: "",
    },
  ];
  private productDialog = false;
  private dialogTitle = "";
  private receiptService;
  private previewHeading = "";
  private item = {
    storeName: "",
    storeAddress: "",
    storeEmail: "",
    storePhone: "",
    storeLicense: "",
    receiptNO: "",
    type: "",
    description: "",
    receiptDate: "",
    receiptDueDate: "",
    selectedProfile: "",
    totalTax1: 0,
    totalTax2: 0,
    totalTax3: 0,
    totalGross: 0,
    totalDiscount: 0,
    totalTax: 0,
    totalBill: 0,
    itemList: [
      {
        accountID: 0,
        accountHead: "",
        quantity: 1,
        price: 0,
        discount: 0,
        subTotal: 0,
        tax1Value: 0,
        tax2Value: 0,
        tax3Value: 0,
      },
    ],
  };

  private taxNames = [
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
  ];

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.toast = new Toaster();
    this.receiptService = new ReceiptService();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.productDialog = true;
  }

  closeDialog() {
    this.$emit("updatePreviewStatus", {});
    this.productDialog = false;
  }

  getCompanyURL() {
    return require("@/assets/images/logo.png").default;
  }

  loadReceipt(receiptID) {
    this.receiptService.getReceiptData(receiptID).then((res) => {
      if (res != null) {
        this.item.storeName = res.storeDetail.name;
        this.item.storeAddress = res.storeDetail.address;
        this.item.storeEmail = res.storeDetail.email;
        this.item.storePhone = res.storeDetail.contact;
        this.item.storeLicense = res.storeDetail.license_no;
        this.item.receiptNO = res.receipt.receipt_no;
        this.item.description = res.receipt.description;
        this.item.receiptDate = res.receipt.receipt_date;
        this.item.receiptDueDate = res.receipt.receipt_due_date;
        this.item.type = res.receipt.type;
        this.item.selectedProfile = res.receipt.profile_name.profileName;
        this.item.totalTax1 = Number(res.receipt.total_tax1);
        this.item.totalTax2 = Number(res.receipt.total_tax2);
        this.item.totalTax3 = Number(res.receipt.total_tax3);
        this.item.totalGross = Number(res.receipt.gross_total);
        this.item.totalDiscount = Number(res.receipt.total_discount);
        this.item.totalTax = Number(res.receipt.total_tax);
        this.item.totalBill = Number(res.receipt.total_bill);

        this.receiptPayments(res.receipt);
        //CALCULATE TOTAL BALANCE
        this.totalBalance = this.calculateBalance(res.receipt.total_bill,res.receipt);

        let vList = res.subReceipt;

        if (vList.length > 0) {
          this.item.itemList = [];

          vList.map((v) => {
            this.item.itemList.push({
              accountID: Number(v.sub_transaction_id),
              accountHead: v.chart_name.chartName,
              quantity: Number(v.qty),
              price: Number(v.price),
              discount: Number(v.discount),
              subTotal: Number(v.sub_total),
              tax1Value: Number(v.tax1),
              tax2Value: Number(v.tax2),
              tax3Value: Number(v.tax3),
            });
          });
        }

        //taxNames
        this.taxNames = [];

        this.taxNames.push({
          taxName: res.storeDetail.tax_name_1,
          show: res.storeDetail.show_1,
          optionalReq: res.storeDetail.required_optional_1,
          taxValue:
            res.storeDetail.show_1 == "true"
              ? Number(res.storeDetail.tax_value_1)
              : 0,
          accountHead: res.storeDetail.tax_name1.chartName,
          accountID: res.storeDetail.link1,
        });

        this.taxNames.push({
          taxName: res.storeDetail.tax_name_2,
          show: res.storeDetail.show_2,
          optionalReq: res.storeDetail.required_optional_2,
          taxValue:
            res.storeDetail.show_2 == "true"
              ? Number(res.storeDetail.tax_value_2)
              : 0,
          accountHead: res.storeDetail.tax_name2.chartName,
          accountID: res.storeDetail.link2,
        });

        this.taxNames.push({
          taxName: res.storeDetail.tax_name_3,
          show: res.storeDetail.show_3,
          optionalReq: res.storeDetail.required_optional_3,
          taxValue:
            res.storeDetail.show_3 == "true"
              ? Number(res.storeDetail.tax_value_3)
              : 0,
          accountHead: res.storeDetail.tax_name3.chartName,
          accountID: res.storeDetail.link3,
        });
      }
    });
  }

  printReceipt() {
    window.print();
  }

  calculateBalance(totalBill,data) {
    let totalAmount = 0;

    data.receipt_balance.forEach((e) => {
      totalAmount = totalAmount + Number(e.trans_total_amount);
    });

    return Number(totalBill - totalAmount);
  }

  receiptPayments(data) {
    this.PaymentLists = [];

    data.receipt_balance.forEach((e) => {
      this.PaymentLists.push({
        createdDate: e.created_at,
        receiptNo: e.receipt_no,
        transTotalAmount: e.trans_total_amount,
        description: e.description,
        sourceType: e.trans_type,
        paymentType: e.payment_type,
      });
    });
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.company-logo {
  width: 120px;
  height: auto;
}

.total-lables {
  background-color: #28a745;
  color: #fff;
  font-size: 18px;
  font-weight: bold;
}
</style>