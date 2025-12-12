<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
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
        <Column field="account_type" header="Account Type"></Column>
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
                @click="editItem(slotProps.data)"
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
        :style="{ width: '70vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid p-m-0"
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
                for="balanceDate"
                :class="{ 'p-error': v$.balanceDate.$invalid && submitted }"
                >Balance Date</label
              >
              <Calendar
                id="balanceDate"
                v-model="v$.balanceDate.$model"
                :class="{ 'p-invalid': v$.balanceDate.$invalid && submitted }"
                selectionMode="single"
                dateFormat="dd-mm-yy"
              />
              <span v-if="v$.balanceDate.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.balanceDate.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.balanceDate.$invalid && submitted) ||
                  v$.balanceDate.$pending.$response
                "
                class="p-error"
                >{{
                  v$.balanceDate.required.$message.replace(
                    "Value",
                    "Balance Date"
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
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label
                for="description"
                :class="{ 'p-error': v$.account.$invalid && submitted }"
                >Account</label
              >
              <Dropdown
                id="account"
                v-model="v$.account.$model"
                :options="accountList"
                optionLabel="key"
                optionValue="key"
                :filter="true"
                :class="{ 'p-invalid': v$.account.$invalid && submitted }"
              />
              <small> 
                  Use debit when cash is receivable or use credit when cash is payable.
              </small>
              <br />
              <small
                v-if="
                  (v$.account.$invalid && submitted) ||
                  v$.account.$pending.$response
                "
                class="p-error"
                >{{
                  v$.account.required.$message.replace("Value", "Account")
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label
                for="amount"
                :class="{ 'p-error': v$.amount.$invalid && submitted }"
                >Amount ({{currency}})</label
              >
              <InputNumber
                mode="decimal"
                :useGrouping="false"
                :maxFractionDigits="2"
                :minFractionDigits="2"
                id="amount"
                v-model="v$.amount.$model"
                :class="{ 'p-invalid': v$.amount.$invalid && submitted }"
              />
              <span v-if="v$.amount.$error && submitted">
                <span
                  id="p-error"
                  v-for="(error, index) of v$.amount.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
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
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            :disabled="item.profileID == 0 || state.amount <= 0"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="saveItem(!v$.$invalid)"
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
import BalanceService from "../../service/BalanceService.js";
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
  title: 'User Balance',
  components: {
    AutoComplete,
    SearchFilter,
    PreviewReceipt,
  },
})
export default class UserBalance extends Vue {
  private lists = [];
  private profilerList = [];
  private store = useStore();
  private statement = "";
  private dialogTitle = "";
  private toast;
  private goToFirstLink = 0;
  private previewHeading = "";
  private receiptID = 0;
  private balanceService;
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
    { label: "User Balances", to: "user-balance" },
  ];

  private itemFilter = {
    keyword: "",
    filterType: "None",
    storeID: 0,
    date1: "",
    date2: "",
    type: "OPB",
  };

  private item = {
    id: 0,
    transactionID: 0,
    status: "Active",
    profileID: 0,
    type: "OPB",
    itemList: [
      {
        accountID: 0,
        accountHead: "",
        debitAmount: 0,
        creditAmount: 0,
      },
    ],
  };

  private accountList = [{ key: "Debit" }, { key: "Credit" }];

  private state = reactive({
    description: "",
    balanceDate: "",
    selectedProfile: "",
    amount: 0,
    account: "Debit",
  });

  private validationRules = {
    description: {
      required,
    },
    balanceDate: {
      required,
    },
    selectedProfile: {
      required,
    },
    amount: {
      required,
    },
    account: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.balanceService = new BalanceService();
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
    this.dialogTitle = "Create User Balance";
    this.productDialog = true;
  }

  openFilterDialog() {
    this.dialogTitle = "Filter User Balance";
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
    if (isFormValid) {
      this.setAccountingEntries();

      if (this.item.id != 0) {
        this.state.balanceDate = moment(this.state.balanceDate).format(
          "YYYY-MM-DD"
        );

        this.balanceService.update(this.item, this.state).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        this.state.balanceDate = moment(this.state.balanceDate).format(
          "YYYY-MM-DD"
        );

        this.balanceService.save(this.item, this.state).then((res) => {
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
  editItem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Balance";
    this.productDialog = true;

    this.balanceService.getItem(data).then((res) => {
      if (res != null) {
        this.item.id = res.balance.id;
        this.state.selectedProfile = res.balance.profile_name;
        this.state.balanceDate = res.balance.voucher_date;
        this.state.description = res.balance.memo;
        this.item.profileID = res.balance.profile_id;
        this.item.transactionID = res.balance.transaction_id;
        this.state.amount = res.balance.total_amount;
        this.state.account = res.balance.account_type;
        this.item.type = res.balance.type;
        this.item.status = res.balance.status;
      }
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.balanceService.getItems(this.itemFilter, page).then((data) => {
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
    this.state.balanceDate = "";
    this.state.selectedProfile = "";
    this.item.itemList = [];
    this.state.amount = 0;
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
    this.dialogTitle = "Preview User Balance";
    this.previewHeading = "User Balance";
    this.receiptID = data.id;
  }

  updatePreviewStatus() {
    this.previewImageDialog = false;
  }

  setAccountingEntries() {
    if (this.state.account == "Debit") {
      this.item.itemList.push({
        accountID: 4,
        accountHead: "Accounts receivable",
        debitAmount: Number(this.state.amount),
        creditAmount: 0,
      });

      this.item.itemList.push({
        accountID: 4,
        accountHead: "Accounts receivable",
        debitAmount: 0,
        creditAmount: Number(this.state.amount),
      });
    } else {
      this.item.itemList.push({
        accountID: 5,
        accountHead: "Accounts payable",
        debitAmount: Number(this.state.amount),
        creditAmount: 0,
      });

      this.item.itemList.push({
        accountID: 5,
        accountHead: "Accounts payable",
        debitAmount: 0,
        creditAmount: Number(this.state.amount),
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