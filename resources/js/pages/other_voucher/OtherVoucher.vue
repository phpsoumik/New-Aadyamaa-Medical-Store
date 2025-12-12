<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-2">
            <Dropdown
              v-model="itemFilter.type"
              :options="voucherTypes"
              optionLabel="value"
              optionValue="key"
              @change="loadList(0)"
            />
          </div>
          <div class="p-inputgroup">
            <InputText
              v-model.trim="itemFilter.keyword"
              placeholder="Voucher No"
            />
            <Button
              icon="pi pi-search "
              class="p-button-primary p-mr-1"
              @click="loadSearchData"
            />
          </div>
          <div class="p-mx-2">
            <Button
              icon="pi pi-calendar"
              class="p-button-warning"
              @click="openFilterDialog"
            />
          </div>
          <div class="p-mx-2">
            <Button
              icon="pi pi-plus"
              class="p-button-success"
              @click="openDialog"
            />
          </div>
        </template>
      </Toolbar>
      <p class="st-style p-text-center">
        <b>{{ statement }}</b>
      </p>
      <DataTable
        v-model:first.sync="goToFirstLink"
        :value="lists"
        :lazy="true"
        :paginator="checkPagination"
        :rows="limit"
        :totalRecords="totalRecords"
        :scrollable="true"
        @page="onPage($event)"
        class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
      >
        <template #empty>
          <div class="p-text-center p-p-3">No records found</div>
        </template>
        <Column header="Created Date">
          <template #body="slotProps">
            {{ formatDateTime(slotProps.data.created_at) }}
          </template>
        </Column>
        <Column header="Updated Date">
          <template #body="slotProps">
            {{ formatDateTime(slotProps.data.updated_at) }}
          </template>
        </Column>
        <Column header="Voucher Date">
          <template #body="slotProps">
            {{ formatDate(slotProps.data.voucher_date) }}
          </template>
        </Column>
        <Column field="voucher_no" header="Voucher No"></Column>
        <Column field="profile_name" header="Account Title"></Column>
        <Column header="Total Amount">
          <template #body="slotProps">
            {{currency}} {{ fixDigits(slotProps.data.total_amount) }}
          </template>
        </Column>
        <Column field="memo" header="Description"></Column>
        <Column field="type" header="Voucher Type"></Column>
        <Column field="user_name.userName" header="Created By"></Column>
        <Column header="Store Name">
          <template #body="slotProps">
            {{ slotProps.data.branch.branchName }} (
            {{ slotProps.data.branch.branchCode }} )
          </template>
        </Column>
        <Column :exportable="false" header="Action">
          <template #body="slotProps">
            <span class="p-buttonset">
              <Button
                icon="pi pi-pencil"
                class="p-button-rounded p-button-success"
                @click="editIem(slotProps.data)"
              />
              <Button
                icon="pi pi-print"
                class="p-button-rounded p-button-primary"
                @click="openPreviewDialog(slotProps.data)"
              />
            </span>
          </template>
        </Column>
      </DataTable>
      <Dialog
        v-model:visible="productDialog"
        :style="{ width: '100vw' }"
        position="top"
        class="p-fluid p-m-0 p-dialog-maximized"
      >
        <template #header>
          <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
            {{ dialogTitle }}
          </h5>
        </template>
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="type">Voucher Type</label>
              <Dropdown
                id="type"
                v-model="item.type"
                :options="voucherTypes"
                optionLabel="value"
                optionValue="key"
              />
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label
                for="selectedProfile"
                :class="{ 'p-error': v$.selectedProfile.$invalid && submitted }"
                >Account Holders</label
              >
              <AutoComplete
                :delay="1000"
                :minLength="3"
                @item-select="saveProfile($event)"
                scrollHeight="500px"
                v-model="v$.selectedProfile.$model"
                :suggestions="profilerList"
                placeholder="Search Profile"
                @complete="searchProfiler($event)"
                :dropdown="false"
                autoFocus
              >
                <template #item="slotProps">
                  <div>
                    TITLE :
                    <b class="pull-right">
                      {{ slotProps.item.account_title.toUpperCase() }}
                    </b>
                  </div>
                  <div>
                    Email :
                    <span class="pull-right">
                      {{ slotProps.item.email_address }}
                    </span>
                  </div>
                  <div>
                    Contact :
                    <span class="pull-right">
                      {{ slotProps.item.contact_no }}
                    </span>
                  </div>
                  <div>
                    Account Type :
                    <span class="pull-right">
                      {{ slotProps.item.account_type }}
                    </span>
                  </div>
                </template>
              </AutoComplete>
              <span v-if="v$.selectedProfile.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.selectedProfile.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.selectedProfile.$invalid && submitted) ||
                  v$.selectedProfile.$pending.$response
                "
                class="p-error"
                >{{
                  v$.selectedProfile.required.$message.replace(
                    "Value",
                    "Account Holder"
                  )
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label
                for="voucherDate"
                :class="{ 'p-error': v$.voucherDate.$invalid && submitted }"
                >Voucher Date</label
              >
              <Calendar
                id="voucherDate"
                v-model="v$.voucherDate.$model"
                :class="{ 'p-invalid': v$.voucherDate.$invalid && submitted }"
                selectionMode="single"
                dateFormat="dd-mm-yy"
              />
              <span v-if="v$.voucherDate.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.voucherDate.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.voucherDate.$invalid && submitted) ||
                  v$.voucherDate.$pending.$response
                "
                class="p-error"
                >{{
                  v$.voucherDate.required.$message.replace(
                    "Value",
                    "Voucher Date"
                  )
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label
                for="description"
                :class="{ 'p-error': v$.description.$invalid && submitted }"
                >Description</label
              >
              <InputText
                id="description"
                v-model="v$.description.$model"
                :class="{ 'p-invalid': v$.description.$invalid && submitted }"
              />
              <span v-if="v$.description.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.description.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.description.$invalid && submitted) ||
                  v$.description.$pending.$response
                "
                class="p-error"
                >{{
                  v$.description.required.$message.replace(
                    "Value",
                    "Description"
                  )
                }}</small
              >
            </div>
          </div>
        </div>
        <div class="p-field">
          <DataTable
            :value="state.itemList"
            class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
            responsiveLayout="scroll"
          >
            <Column header="ACCOUNT NAME" style="width: 75%">
              <template #body="slotProps">
                <AutoComplete
                  :delay="1000"
                  :minLength="3"
                  @item-select="saveAccountHead($event, slotProps.data)"
                  scrollHeight="500px"
                  v-model="slotProps.data.accountHead"
                  :suggestions="accountHeadList"
                  placeholder="Search Account"
                  @complete="searchAccountHead($event)"
                  :dropdown="false"
                  :class="{
                    'p-invalid':
                      this.validateHeadList.includes(
                        state.itemList.indexOf(slotProps.data)
                      ) && submitted,
                  }"
                  class="p-p-1"
                >
                  <template #item="slotProps">
                    <div>
                      Head Code :
                      <b class="pull-right">
                        {{ slotProps.item.account_code.toUpperCase() }}
                      </b>
                    </div>
                    <div>
                      Head Name :
                      <b class="pull-right">
                        {{ slotProps.item.account_name }}
                      </b>
                    </div>
                    <div>
                      Nature :
                      <span class="pull-right">
                        {{ slotProps.item.account_nature }}
                      </span>
                    </div>
                    <div>
                      Head Type :
                      <span class="pull-right">
                        {{ slotProps.item.account_type }}
                      </span>
                    </div>
                  </template>
                </AutoComplete>
              </template>
            </Column>
            <Column :header="'AMOUNT ('+currency+')'" style="width: 20%">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.totalAmount"
                  class="p-p-1"
                />
              </template>
            </Column>
            <Column header="Action" style="width: 5%">
              <template #body="slotProps">
                <Button
                  type="button"
                  icon="pi pi-times"
                  class="p-button-danger pull-left"
                  @click="clearListItem(slotProps.data)"
                />
              </template>
            </Column>
          </DataTable>
        </div>
        <template #footer>
          <div class="p-grid">
            <div class="p-col-8 p-text-left">
              <Button
                type="button"
                label="Clear All Items"
                icon="pi pi-times"
                class="p-button-danger pull-left"
                @click="clearLines()"
              />
              <Button
                type="button"
                :label="'Total Amount : '+currency+' ' + fixDigits(totalVoucherAmount)"
                class="p-button-warning"
              />
            </div>
            <div class="p-col-4 p-text-right">
              <Button
                type="button"
                label="Add New Line"
                icon="pi pi-plus-circle"
                class="p-button-success"
                @click="addNewLine()"
              />
              <Button
                type="submit"
                label="Save"
                :disabled="
                  item.profileID == 0 ||
                  state.itemList.length <= 0 ||
                  totalVoucherAmount <= 0
                "
                icon="pi pi-check"
                class="p-button-primary"
                @click.prevent="saveItem(!v$.$invalid)"
              />
            </div>
          </div>
        </template>
      </Dialog>
      <SearchFilter
        :searchDetail="{
          status: this.filterDialog,
          dialogTitle: this.dialogTitle,
        }"
        v-on:updateFilterStatus="updateFilterStatus"
      />

      <PreviewReceipt
        :PreviewReceipt="{
          status: this.previewImageDialog,
          dialogTitle: this.dialogTitle,
          previewHeading: this.previewHeading,
          receiptID: this.receiptID,
        }"
        v-on:updatePreviewStatus="updatePreviewStatus"
      />
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import VoucherService from "../../service/VoucherService.js";
import ProfilerService from "../../service/ProfilerService.js";
import ChartService from "../../service/ChartService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, minLength, maxLength } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import AutoComplete from "primevue/autocomplete";
import SearchFilter from "../../components/SearchFilter.vue";
import PreviewReceipt from "../../components/PreviewReceipt.vue";
import { useStore, ActionTypes } from "../../store";

@Options({
  title: 'Other Voucher',
  components: {
    AutoComplete,
    SearchFilter,
    PreviewReceipt,
  },
})
export default class OtherVoucher extends Vue {
  private lists = [];
  private profilerList = [];
  private store = useStore();
  private accountHeadList = [];
  private statement = "";
  private dialogTitle = "";
  private toast;
  private goToFirstLink = 0;
  private previewHeading = "";
  private receiptID = 0;
  private voucherService;
  private profilerService;
  private chartService;
  private previewImageDialog = false;
  private productDialog = false;
  private filterDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Other Vouchers", to: "other-voucher" },
  ];

  private itemFilter = {
    keyword: "",
    filterType: "None",
    storeID: 0,
    date1: "",
    date2: "",
    type: "DBV",
  };

  private item = {
    id: 0,
    transactionID: 0,
    status: "Active",
    profileID: 0,
    totalAmount: 0,
    type: "DBV",
  };

  private state = reactive({
    description: "",
    voucherDate: "",
    selectedProfile: "",
    itemList: [
      {
        accountID: 0,
        accountHead: "",
        totalAmount: 0,
        type: "",
      },
    ],
  });

  private validationRules = {
    description: {
      required,
    },
    voucherDate: {
      required,
    },
    selectedProfile: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  private voucherTypes = [
    { key: "DBV", value: "Debit Voucher" },
    { key: "CRV", value: "Credit Voucher" },
    { key: "EXV", value: "Expense Voucher" },
  ];

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.voucherService = new VoucherService();
    this.profilerService = new ProfilerService();
    this.chartService = new ChartService();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.clearItem();
    this.submitted = false;
    this.dialogTitle = "Create Voucher";
    this.productDialog = true;
  }

  openFilterDialog() {
    this.dialogTitle = "Filter Vouchers";
    this.filterDialog = true;
  }

  //CLOSE THE ITEM DAILOG BOX
  hideDialog() {
    this.productDialog = false;
    this.submitted = false;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid && this.validateHeadList.length == 0) {
      this.setAccountingEntries();

      if (this.item.id != 0) {
        this.state.voucherDate = moment(this.state.voucherDate).format(
          "YYYY-MM-DD"
        );

        this.voucherService.update(this.item, this.state).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        this.state.voucherDate = moment(this.state.voucherDate).format(
          "YYYY-MM-DD"
        );

        this.voucherService.save(this.item, this.state).then((res) => {
          this.goToFirstLink = 0;
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      }

      this.productDialog = false;
      this.clearItem();
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Voucher";
    this.productDialog = true;

    this.voucherService.getItem(data).then((res) => {
      if (res != null) {
        this.item.id = res.voucher.id;
        this.state.selectedProfile = res.voucher.profile_name;
        this.state.voucherDate = res.voucher.voucher_date;
        this.state.description = res.voucher.memo;
        this.item.profileID = res.voucher.profile_id;
        this.item.transactionID = res.voucher.transaction_id;
        this.item.totalAmount = res.voucher.total_amount;
        this.item.type = res.voucher.type;
        this.item.status = res.voucher.status;

        let vList = res.voucherList;
        if (vList.length > 0) {
          this.state.itemList = [];
          vList.map((v) => {
            if (
              v.type == "Debit" &&
              (res.voucher.type == "DBV" || res.voucher.type == "EXV")
            ) {
              this.state.itemList.push({
                accountID: Number(v.account_id),
                accountHead: v.account_name,
                totalAmount: Number(v.amount),
                type: v.account_type,
              });
            } else if (v.type == "Credit" && res.voucher.type == "CRV") {
              this.state.itemList.push({
                accountID: Number(v.account_id),
                accountHead: v.account_name,
                totalAmount: Number(v.amount),
                type: v.account_type,
              });
            } else {
              //NO THING
            }
          });
        }
      }
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.voucherService.getItems(this.itemFilter, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.statement = data.statement;
    });
  }

  clearItem() {
    this.item.id = 0;
    this.item.profileID = 0;
    this.item.status = "Active";
    this.state.description = "";
    this.state.voucherDate = "";
    this.state.selectedProfile = "";

    this.state.itemList = [];
    this.state.itemList.push({
      accountID: 0,
      accountHead: "",
      totalAmount: 0,
      type: "",
    });
  }

  loadSearchData() {
    this.submitted = true;
    if (this.itemFilter.keyword) {
      this.goToFirstLink = 0;
      this.loadList(this.goToFirstLink);
    }
  }

  searchProfiler(event) {
    setTimeout(() => {
      this.profilerService.searchProfiler(event.query.trim()).then((data) => {
        this.profilerList = data.records;
      });
    }, 500);
  }

  saveProfile(event) {
    const profileInfo = event.value;
    this.state.selectedProfile = profileInfo.account_title;
    this.item.profileID = profileInfo.id;
  }

  searchAccountHead(event) {
    setTimeout(() => {
      this.chartService.searchAccountHead(event.query.trim()).then((data) => {
        this.accountHeadList = data.records;
      });
    }, 500);
  }

  saveAccountHead(event, data) {
    const accountInfo = event.value;
    data.accountHead = accountInfo.account_name;
    data.accountID = accountInfo.id;
  }

  addNewLine() {
    this.state.itemList.push({
      accountID: 0,
      accountHead: "",
      totalAmount: 0,
      type: "",
    });
  }

  clearLines() {
    this.state.itemList = [];
    this.state.itemList.push({
      accountID: 0,
      accountHead: "",
      totalAmount: 0,
      type: "",
    });

    this.toast.showSuccess("All Items Deleted Successfully");
  }

  clearListItem(item) {
    this.state.itemList.splice(this.state.itemList.indexOf(item), 1);
    this.toast.showSuccess("Item Deleted Successfully");
  }

  get totalVoucherAmount() {
    let totalAmount = 0;
    this.state.itemList.forEach((v) => {
      totalAmount = totalAmount + Number(v.totalAmount);
    });

    this.item.totalAmount = totalAmount;

    return totalAmount;
  }

  get validateHeadList() {
    let invalidListItems: Number[] = [];

    this.state.itemList.map((v, index) => {
      if (v.accountID == 0) {
        invalidListItems.push(index);
      }
    });

    return invalidListItems;
  }

  fixDigits(amt) {
    return Number(amt).toFixed(2);
  }

  formatDate(date) {
    return moment(date).format("DD-MM-YYYY");
  }

  formatDateTime(date) {
    return moment(date).format("DD-MM-YYYY hh:mm A");
  }

  updateFilterStatus(obj) {
    if (obj != null && obj.loading == "Yes") {
      this.itemFilter.filterType = obj.filterName.value;
      this.itemFilter.storeID = obj.storeID.id;
      this.itemFilter.date1 = obj.date1;
      this.itemFilter.date2 = obj.date2;
      this.itemFilter.keyword = "";
      this.goToFirstLink = 0;
      this.loadList(this.goToFirstLink);
    }
    this.filterDialog = false;
  }

  openPreviewDialog(data) {
    this.previewImageDialog = true;

    if (data.type == "DBV") {
      this.dialogTitle = "Preview Debit Voucher";
      this.previewHeading = "Debit Voucher";
    } else if (data.type == "CRV") {
      this.dialogTitle = "Preview Credit Receipt";
      this.previewHeading = "Credit Voucher";
    } else {
      this.dialogTitle = "Preview Expense Voucher";
      this.previewHeading = "Expense Voucher";
    }

    this.receiptID = data.id;
  }

  updatePreviewStatus() {
    this.previewImageDialog = false;
  }

  setAccountingEntries() {
    if (this.item.type == "EXV") {
      this.state.itemList.forEach((e) => {
        e.type = "Debit";
      });

      this.state.itemList.unshift({
        accountID: 2,
        accountHead: "Cash in hand",
        totalAmount: this.totalVoucherAmount,
        type: "Credit",
      });
    } else if (this.item.type == "DBV") {
      this.state.itemList.forEach((e) => {
        e.type = "Debit";
      });

      this.state.itemList.unshift({
        accountID: 2,
        accountHead: "Cash in hand",
        totalAmount: this.totalVoucherAmount,
        type: "Credit",
      });
    } else if (this.item.type == "CRV") {
      this.state.itemList.forEach((e) => {
        e.type = "Credit";
      });

      this.state.itemList.push({
        accountID: 2,
        accountHead: "Cash in hand",
        totalAmount: this.totalVoucherAmount,
        type: "Debit",
      });
    }
  }

   get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.st-style {
  background-color: #f9f9f9;
  color: #000;
  font-size: 14px;
  padding: 5px;
  margin: 0;
}
</style>