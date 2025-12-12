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
              :options="bankingTypes"
              optionLabel="key"
              optionValue="value"
              @change="loadList(0)"
            />
          </div>
          <div class="p-inputgroup">
            <InputText
              v-model.trim="itemFilter.keyword"
              placeholder="Receipt No"
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
        dataKey="id"
        ref="dt"
        :lazy="true"
        :paginator="checkPagination"
        :rows="limit"
        :totalRecords="totalRecords"
        :resizableColumns="true"
        columnResizeMode="expand"
        responsiveLayout="scroll"
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
        <Column header="Receipt Date">
          <template #body="slotProps">
            {{ formatDate(slotProps.data.receipt_date) }}
          </template>
        </Column>
        <Column field="receipt_no" header="Receipt No"></Column>
        <Column header="Account Title">
          <template #body="slotProps">
            {{ slotProps.data.profile_name.profileName }}
          </template>
        </Column>
        <Column header="Total Amount">
          <template #body="slotProps">
            {{currency}} {{ fixDigits(slotProps.data.amount) }}
          </template>
        </Column>
        <Column field="description" header="Description"></Column>
        <Column field="user_name.userName" header="Created By"></Column>
        <Column header="Store Name">
          <template #body="slotProps">
            {{ slotProps.data.branch.branchName }} (
            {{ slotProps.data.branch.branchCode }} )
          </template>
        </Column>
        <Column header="Status">
          <template #body="slotProps">
            {{ slotProps.data.status }}
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
              <Button
                icon="pi pi-cog"
                class="p-button-rounded p-button-warning"
                v-if="slotProps.data.type == 'CHQ'"
                @click="openActivityDialog(slotProps.data)"
              />
            </span>
          </template>
        </Column>
      </DataTable>

      <Dialog
        v-model:visible="productDialog"
        :style="{ width: '70vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid"
      >
        <template #header>
          <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
            {{ dialogTitle }}
          </h5>
        </template>
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label
                for="type"
                :class="{ 'p-error': v$.type.$invalid && submitted }"
                >Activity Type</label
              >
              <Dropdown
                id="type"
                v-model="v$.type.$model"
                :options="bankingTypes"
                optionLabel="key"
                @change="setDefaultProfiler"
                autoFocus
              />
              <small
                v-if="
                  (v$.type.$invalid && submitted) || v$.type.$pending.$response
                "
                class="p-error"
                >{{ v$.type.required.$message.replace("Value", "Type") }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="receiptDate"
                :class="{ 'p-error': v$.receiptDate.$invalid && submitted }"
                >Receipt Date</label
              >
              <Calendar
                id="receiptDate"
                v-model="v$.receiptDate.$model"
                :class="{ 'p-invalid': v$.receiptDate.$invalid && submitted }"
                selectionMode="single"
                dateFormat="dd-mm-yy"
              />
              <span v-if="v$.receiptDate.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.receiptDate.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.receiptDate.$invalid && submitted) ||
                  v$.receiptDate.$pending.$response
                "
                class="p-error"
                >{{
                  v$.receiptDate.required.$message.replace("Value", "Date")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="bankID"
                :class="{ 'p-error': v$.bankID.$invalid && submitted }"
                >Bank</label
              >
              <Dropdown
                id="bankID"
                v-model="v$.bankID.$model"
                :options="bankList"
                optionLabel="bank"
                optionValue="id"
                :filter="true"
                :class="{ 'p-invalid': v$.bankID.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.bankID.$invalid && submitted) ||
                  v$.bankID.$pending.$response
                "
                class="p-error"
                >{{
                  v$.bankID.required.$message.replace("Value", "Bank")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="amount"
                :class="{ 'p-error': v$.amount.$invalid && submitted }"
                > Amount ({{currency}})</label
              >
              <InputNumber
                :useGrouping="false"
                mode="decimal"
                :maxFractionDigits="2"
                :minFractionDigits="2"
                id="amount"
                v-model="v$.amount.$model"
                :class="{ 'p-invalid': v$.amount.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.amount.$invalid && submitted) ||
                  v$.amount.$pending.$response
                "
                class="p-error"
                >{{
                  v$.amount.required.$message.replace("Value", "Amount")
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field" v-if="v$.type.$model.key != 'Bank Deposit' && v$.type.$model.key != 'Bank Expense'">
              <label
                for="selectedProfile"
                :class="{ 'p-error': v$.selectedProfile.$invalid && submitted }"
                >Account Holder</label
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
                :class="{
                  'p-invalid': v$.selectedProfile.$invalid && submitted,
                }"
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
            <div class="p-field">
              <label
                for="accountHead"
                :class="{ 'p-error': v$.accountHead.$invalid && submitted }"
                >Account Head</label
              >
              <AutoComplete
                :delay="1000"
                :minLength="3"
                @item-select="saveAccountHead($event)"
                scrollHeight="500px"
                v-model="v$.accountHead.$model"
                :suggestions="accountHeadList"
                placeholder="Search Account"
                @complete="searchAccountHead($event)"
                :dropdown="false"
                :class="{ 'p-invalid': v$.accountHead.$invalid && submitted }"
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
              <span v-if="v$.accountHead.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.accountHead.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.accountHead.$invalid && submitted) ||
                  v$.accountHead.$pending.$response
                "
                class="p-error"
                >{{
                  v$.accountHead.required.$message.replace(
                    "Value",
                    "Account Head"
                  )
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="receiptNo"
                :class="{ 'p-error': v$.receiptNo.$invalid && submitted }"
                >Cheque or Transaction No</label
              >
              <InputText
                id="receiptNo"
                v-model="v$.receiptNo.$model"
                :class="{ 'p-invalid': v$.receiptNo.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.receiptNo.$invalid && submitted) ||
                  v$.receiptNo.$pending.$response
                "
                class="p-error"
                >{{
                  v$.receiptNo.required.$message.replace(
                    "Value",
                    "Cheque or Transaction No"
                  )
                }}</small
              >
            </div>
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
              <small
                v-if="
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
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="saveItem(!v$.$invalid)"
            :disabled="
              (item.profileID == 0 && v$.selectedProfile.$model != 'None') ||
              item.accountID == 0 ||
              v$.amount.$model <= 0
            "
          />
        </template>
      </Dialog>
      <Dialog
        v-model:visible="bankingActionDialog"
        :style="{ width: '40vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid"
      >
        <template #header>
          <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
            Update cheque status
          </h5>
        </template>
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="type">Activity Type</label>
              <Dropdown
                v-model="item.status"
                :options="chequeActivity"
                optionLabel="name"
                optionValue="name"
              />
            </div>
          </div>
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="updateBankActivity()"
            :disabled="item.id == 0"
          />
        </template>
      </Dialog>
      <SearchFilter
        :searchDetail="{
          status: this.filterDialog,
          dialogTitle: this.dialogTitle,
        }"
        v-on:updateFilterStatus="updateFilterStatus"
      />

      <PreviewBankingReceipt
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
import BankingService from "../../service/BankingService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import AutoComplete from "primevue/autocomplete";
import SearchFilter from "../../components/SearchFilter.vue";
import ChartService from "../../service/ChartService.js";
import ProfilerService from "../../service/ProfilerService.js";
import PreviewBankingReceipt from "../../components/PreviewBankingReceipt.vue";
import { useStore, ActionTypes } from "../../store";

@Options({
  title: 'Banking',
  components: { AutoComplete, SearchFilter, PreviewBankingReceipt },
})
export default class Banking extends Vue {
  private lists = [];
  private profilerList = [];
  private accountHeadList = [];
  private bankList = [];
  private statement = "";
  private dialogTitle = "";
  private previewHeading = "";
  private receiptID = 0;
  private toast;
  private goToFirstLink = 0;
  private bankService;
  private bankingService;
  private chartService;
  private profilerService;
  private productDialog = false;
  private bankingActionDialog = false;
  private previewImageDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private filterDialog = false;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Banking" , to: "banking" },
  ];
  private store = useStore();


  private item = {
    id: 0,
    status: "Active",
    profileID: 0,
    accountID: 0,
    branchID: 0,
  }; 
  
  private chequeActivity = [
    {name: "Outstanding"},
    {name: "Active"}
  ];

  private itemFilter = {
    keyword: "",
    filterType: "None",
    storeID: 0,
    date1: "",
    date2: "",
    type: "CHQ",
  };

  private state = reactive({
    bankID: 0,
    selectedProfile: "",
    receiptNo: "",
    accountHead: "",
    amount: 0,
    receiptDate: "",
    description: "",
    type: { key: "Cheque", value: "CHQ" },
    itemList: [
      {
        accountID: 0,
        accountHead: "",
        amount: 0,
        type: "",
      },
    ],
  });

  private bankingTypes = [
    { key: "Cheque", value: "CHQ" },
    { key: "Bank Deposit", value: "DPT" },
    { key: "Fund Transfer", value: "FTR" },
    { key: "Receive Fund", value: "REF" },
    { key: "Bank Expense", value: "EXP" },
  ];

  private validationRules = {
    type: {
      required,
    },
    bankID: {
      required,
    },
    receiptNo: {
      required,
    },
    selectedProfile: {
      required,
    },
    accountHead: {
      required,
    },
    amount: {
      required,
    },
    receiptDate: {
      required,
    },
    description: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.bankingService = new BankingService();
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
    this.clearItems();
    this.submitted = false;
    this.dialogTitle = "Create Banking Activity";
    this.productDialog = true;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      this.setAccountingEntries();

      if (this.item.id != 0) {
        this.state.receiptDate = moment(this.state.receiptDate).format(
          "YYYY-MM-DD"
        );
        this.bankingService.updateItem(this.item, this.state).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        this.state.receiptDate = moment(this.state.receiptDate).format(
          "YYYY-MM-DD"
        );
        this.bankingService.saveItem(this.item, this.state).then((res) => {
          this.goToFirstLink = 0;
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      }
      this.productDialog = false;
      this.clearItems();
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Banking Activity";
    this.productDialog = true;
    this.bankingService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.bankID = res[0].bank_id;
        this.state.selectedProfile = res[0].profile_name.profileName;
        this.state.receiptNo = res[0].transaction_no;
        this.state.accountHead = res[0].account_head;
        this.state.amount = Number(res[0].amount);
        this.state.receiptDate = res[0].receipt_date;
        this.state.description = res[0].description;
        this.state.type = this.getTheType(res[0].type);
        this.item.id = res[0].id;
        this.item.status = res[0].status;
        this.item.profileID = res[0].profile_name.id;
        this.item.accountID = res[0].account_id;
      }
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.bankingService.getItems(this.itemFilter, page).then((data) => {
      this.lists = data.records;
      this.statement = data.statement;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.bankList = data.banks;
    });
  }

  clearItems() {
    this.item.id = 0;
    this.item.status = "Active";
    this.item.profileID = 0;
    this.item.accountID = 0;

    this.state.bankID = 0;
    this.state.selectedProfile = "";
    this.state.accountHead = "";
    this.state.amount = 0;
    this.state.receiptDate = "";
    this.state.description = "";
    this.state.receiptNo = "";
    this.state.type = { key: "Cheque", value: "CHQ" };

    this.state.itemList = [
      {
        accountID: 0,
        accountHead: "",
        amount: 0,
        type: "",
      },
    ];
  }

  searchAccountHead(event) {
    setTimeout(() => {
      this.chartService.searchAccountHead(event.query.trim()).then((data) => {
        this.accountHeadList = data.records;
      });
    }, 500);
  }

  saveAccountHead(event) {
    const accountInfo = event.value;
    this.state.accountHead = accountInfo.account_name;
    this.item.accountID = accountInfo.id;
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

  fixDigits(amt) {
    return Number(amt).toFixed(2);
  }

  formatDate(date) {
    return moment(date).format("DD-MM-YYYY");
  }

  formatDateTime(date) {
    return moment(date).format("DD-MM-YYYY hh:mm A");
  }

  openFilterDialog() {
    this.dialogTitle = "Filter Cheque/Fund Transfer/Receive Fund";
    this.filterDialog = true;
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

  loadSearchData() {
    this.submitted = true;
    if (this.itemFilter.keyword) {
      this.goToFirstLink = 0;
      this.loadList(this.goToFirstLink);
    }
  }

  setAccountingEntries() {
    this.state.itemList = [];

    if (this.state.type.value == "CHQ" || this.state.type.value == "FTR" || this.state.type.value == "EXP") {
      this.state.itemList.push({
        accountID: this.item.accountID,
        accountHead: this.state.accountHead,
        amount: this.state.amount,
        type: "Debit",
      });

      this.state.itemList.push({
        accountID: 8,
        accountHead: "Cash in Bank",
        amount: this.state.amount,
        type: "Credit",
      });
    } else if (
      this.state.type.value == "REF" ||
      this.state.type.value == "DPT"
    ) {
      this.state.itemList.push({
        accountID: 8,
        accountHead: "Cash in Bank",
        amount: this.state.amount,
        type: "Debit",
      });

      this.state.itemList.push({
        accountID: this.item.accountID,
        accountHead: this.state.accountHead,
        amount: this.state.amount,
        type: "Credit",
      });
    } else {
      this.state.itemList = [
        {
          accountID: 0,
          accountHead: "",
          amount: 0,
          type: "",
        },
      ];
    }
  }

  setDefaultProfiler() {
    if (this.state.type.key == "Bank Deposit" || this.state.type.key == "Bank Expense") {
      this.state.selectedProfile = "None";
      this.item.profileID = 0;
    }
  }

  getTheType(type) {
    let obj = { key: "", value: "" };
    this.bankingTypes.forEach((v) => {
      if (v.value == type) {
        obj.key = v.key;
        obj.value = v.value;
      }
    });

    return obj;
  }

  openPreviewDialog(data) {
    if (data.type == "CHQ") {
      this.dialogTitle = "Preview Bank Cheque";
      this.previewHeading = "Bank Cheque";
    } else if (data.type == "DPT") {
      this.dialogTitle = "Preview Bank Deposit";
      this.previewHeading = "Bank Deposit";
    } else if (data.type == "FTR") {
      this.dialogTitle = "Preview Fund Transfer";
      this.previewHeading = "Fund Transfer";
    } else if (data.type == "REF") {
      this.dialogTitle = "Preview Receive Fund";
      this.previewHeading = "Receive Fund";
    }
    else if (data.type == "EXP")
    {
      this.dialogTitle = "Preview Bank Expense";
      this.previewHeading = "Bank Expense";
    } else {
      this.dialogTitle = "None";
      this.previewHeading = "None";
    }

    this.previewImageDialog = true;
    this.receiptID = data.id;
  }

  updatePreviewStatus() {
    this.previewImageDialog = false;
  }

  openActivityDialog(d)
  {
    this.item.id = d.id;
    this.item.status = d.status;
    this.bankingActionDialog = true;
  }

  updateBankActivity()
  {
    this.bankingActionDialog = false;
    this.bankingService.updateBankStatus(this.item).then((res) => {
      this.loadList(this.goToFirstLink);
      this.toast.handleResponse(res);
    });
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