<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-2">
            <!-- <Dropdown
              v-model="selectedStore"
              :options="storeList"
              optionLabel="name"
              @change="loadList(0)"
            /> -->
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
      <div class="p-mt-2">
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
          <Column
            v-for="(col, index) of selectedColumns"
            :field="col.field"
            :header="col.header"
            :key="col.field + '_' + index"
          ></Column>
          <Column :exportable="false" header="Action">
            <template #body="slotProps">
              <Button
                icon="pi pi-pencil"
                class="p-button-rounded p-button-primary p-mr-2"
                @click="editIem(slotProps.data)"
              />
              <Button
                icon="pi pi-trash"
                class="p-button-rounded p-button-danger"
                @click="confirmChangeStatus(slotProps.data)"
              />
            </template>
          </Column>
        </DataTable>
      </div>

      <Dialog
        v-model:visible="productDialog"
        :style="{ width: '50vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid"
      >
        <template #header>
          <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
            {{ dialogTitle }}
          </h4>
        </template>
        <div class="p-field">
          <label
            for="bankName"
            :class="{ 'p-error': v$.bankName.$invalid && submitted }"
            >Bank name</label
          >
          <InputText
            id="bankName"
            v-model="v$.bankName.$model"
            :class="{ 'p-invalid': v$.bankName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="
              (v$.bankName.$invalid && submitted) ||
              v$.bankName.$pending.$response
            "
            class="p-error"
            >{{
              v$.bankName.required.$message.replace("Value", "Bank name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="branchName"
            :class="{ 'p-error': v$.branchName.$invalid && submitted }"
            >Branch name</label
          >
          <InputText
            id="branchName"
            v-model="v$.branchName.$model"
            :class="{ 'p-invalid': v$.branchName.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.branchName.$invalid && submitted) ||
              v$.branchName.$pending.$response
            "
            class="p-error"
            >{{
              v$.branchName.required.$message.replace("Value", "Branch name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="code"
            :class="{ 'p-error': v$.code.$invalid && submitted }"
            >Branch Code</label
          >
          <InputText
            id="code"
            v-model="v$.code.$model"
            :class="{ 'p-invalid': v$.code.$invalid && submitted }"
          />
          <small
            v-if="(v$.code.$invalid && submitted) || v$.code.$pending.$response"
            class="p-error"
            >{{
              v$.code.required.$message.replace("Value", "Branch Code")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="title"
            :class="{ 'p-error': v$.title.$invalid && submitted }"
            >Account Title</label
          >
          <InputText
            id="title"
            v-model="v$.title.$model"
            :class="{ 'p-invalid': v$.title.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.title.$invalid && submitted) || v$.title.$pending.$response
            "
            class="p-error"
            >{{
              v$.title.required.$message.replace("Value", "Account Title")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="accountNumber"
            :class="{ 'p-error': v$.accountNumber.$invalid && submitted }"
            >Account Number</label
          >
          <InputText
            id="accountNumber"
            v-model="v$.accountNumber.$model"
            :class="{ 'p-invalid': v$.accountNumber.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.accountNumber.$invalid && submitted) ||
              v$.accountNumber.$pending.$response
            "
            class="p-error"
            >{{
              v$.accountNumber.required.$message.replace(
                "Value",
                "Account Number"
              )
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="type"
            :class="{ 'p-error': v$.type.$invalid && submitted }"
            >Account Type</label
          >
          <Dropdown
            id="type"
            v-model="v$.type.$model"
            :options="accountTypes"
            optionLabel="key"
            @change="resetBalance"
          />
          <small
            v-if="(v$.type.$invalid && submitted) || v$.type.$pending.$response"
            class="p-error"
            >{{
              v$.type.required.$message.replace("Value", "Account Type")
            }}</small
          >
        </div>
        <div class="p-field" v-if="v$.type.$model.key == 'Existing Account'">
          <label for="basic">Statement Ending Date (DD-MM-YYYY)</label>
          <Calendar
            id="basic"
            v-model="item.endingDate"
            selectionMode="single"
            dateFormat="dd-mm-yy"
          />
        </div>
        <div class="p-field" v-if="v$.type.$model.key == 'Existing Account'">
          <label for="balance">Account Balance ({{currency}})</label>
          <InputNumber
            :useGrouping="false"
            id="balance"
            mode="decimal"
            :minFractionDigits="2"
            :maxFractionDigits="2"
            v-model="item.balance"
          />
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="saveItem(!v$.$invalid)"
          />
        </template>
      </Dialog>

      <Dialog
        v-model:visible="statusDialog"
        :style="{ width: '450px' }"
        header="Confirm"
      >
        <div class="confirmation-content">
          <i
            class="pi pi-exclamation-triangle p-mr-3"
            style="font-size: 2rem"
          />
          <span
            >Are you sure to delete <b>{{ state.bankName }}</b> ?</span
          >
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-success"
            @click="statusDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            class="p-button-danger"
            @click="changeStatus"
          />
        </template>
      </Dialog>
    </div>
  </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import BankService from "../../service/BankService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import UtilityOptions from "../../mixins/UtilityOptions";

@Options({
  title: 'Banks',
  components: {},
})
export default class Bank extends mixins(UtilityOptions) {
  private lists = [];
  private dialogTitle;
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private bankService;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Banks", to: "banks" },
  ];

  private columns = [
    { field: "bank", header: "Bank Name" },
    { field: "branch", header: "Branch Name" },
    { field: "code", header: "Branch Code" },
    { field: "title", header: "Account Title" },
    { field: "number", header: "Account Number" },
    { field: "type", header: "Account Type" },
    { field: "ending_date", header: "Statement Date" },
    { field: "balance", header: "Start/Prev Balance" },
  ];

  private selectedColumns = this.columns;

  private item = {
    id: 0,
    endingDate: moment().format('YYYY-MM-DD'),
    balance: 0,
    status: "Active",
  };

  private state = reactive({
    bankName: "",
    branchName: "",
    code: "",
    title: "",
    accountNumber: "",
    type: { key: "New Account" },
  });

  private accountTypes = [{ key: "New Account" }, { key: "Existing Account" }];

  private validationRules = {
    bankName: {
      required,
    },
    branchName: {
      required,
    },
    code: {
      required,
    },
    title: {
      required,
    },
    accountNumber: {
      required,
    },
    type: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  private storeList = [];
  private selectedStore = {
    id: 0,
  };

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.bankService = new BankService();
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
    this.dialogTitle = "Add New Bank";
    this.productDialog = true;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      if (this.item.id != 0) {
        this.item.endingDate = moment(this.item.endingDate).format(
          "YYYY-MM-DD"
        );
        this.bankService.updateItem(this.item, this.state).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {

        this.item.endingDate = moment(this.item.endingDate).format(
          "YYYY-MM-DD"
        );

        this.bankService
          .saveItem(this.item, this.state, this.currentStoreID)
          .then((res) => {
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
    this.dialogTitle = "Update Bank";
    this.productDialog = true;

    this.bankService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.bankName = res[0].bank;
        this.state.branchName = res[0].branch;
        this.state.code = res[0].code;
        this.state.title = res[0].title;
        this.state.accountNumber = res[0].number;
        this.state.type.key = res[0].type;
        this.item.id = res[0].id;
        this.item.endingDate = res[0].ending_date;
        this.item.balance = Number(res[0].balance);
        this.item.status = res[0].status;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.bankName = data.bank;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.bankService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.bankService.getItems(this.selectedStore.id, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.storeList = data.stores;
      this.currentStoreID = data.currentStoreID;
    });
  }

  clearItems() {
    this.item = {
      id: 0,
      endingDate: moment().format("YYYY-MM-DD").toString(),
      balance: 0,
      status: "Active",
    };

    this.state.bankName = "";
    this.state.branchName = "";
    this.state.code = "";
    this.state.title = "";
    this.state.accountNumber = "";
    this.state.type = { key: "New Account" };
  }

  resetBalance() {
    if (this.state.type.key == "New Account") {
      this.item.balance = 0;
    }
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>