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
              v-model.trim="keyword"
              placeholder="Search"
            />
            <Button
              icon="pi pi-search "
              class="p-button-primary p-mr-1"
              @click="loadSearchData"
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
      <div class="p-mt-2">
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
          <Column field="account_code" header="Account Code"></Column>
          <Column field="account_name" header="Account Name"></Column>
          <Column field="account_nature" header="Nature"></Column>
          <Column field="expense_type" header="Expense Type"></Column>
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
            for="accountName"
            :class="{ 'p-error': v$.accountName.$invalid && submitted }"
            >Account name</label
          >
          <InputText
            id="name"
            v-model.trim="v$.accountName.$model"
            :class="{ 'p-invalid': v$.accountName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="(v$.accountName.$invalid && submitted) || v$.accountName.$pending.$response"
            class="p-error"
            >{{
              v$.accountName.required.$message.replace("Value", "Account name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="code"
            :class="{ 'p-error': v$.code.$invalid && submitted }"
            >Account Code</label
          >
          <InputText
            id="code"
            v-model.trim="v$.code.$model"
            :class="{ 'p-invalid': v$.code.$invalid && submitted }"
          />
          <span v-if="v$.code.$error && submitted">
            <span
              id="email-error"
              v-for="(error, index) of v$.code.$errors"
              :key="index"
            >
              <small class="p-error">{{ error.$message }}</small>
            </span>
          </span>
          <small
            v-else-if="
              (v$.code.$invalid && submitted) || v$.code.$pending.$response
            "
            class="p-error"
            >{{ v$.code.required.$message.replace("Value", "Account Code") }}</small
          >
        </div>
        <div class="p-field">
          <label for="nature">Nature</label>
          <Dropdown
            v-model="state.nature"
            id="nature"
            :options="accountNature"
            optionLabel="key"
            @change="setExpenseType"
          />
           <span v-if="v$.nature.$error && submitted">
            <span
              id="email-error"
              v-for="(error, index) of v$.nature.$errors"
              :key="index"
            >
              <small class="p-error">{{ error.$message }}</small>
            </span>
          </span>
          <small
            v-else-if="
              (v$.nature.$invalid && submitted) || v$.nature.$pending.$response
            "
            class="p-error"
            >{{ v$.nature.required.$message.replace("Value", "Account Nature") }}</small
          >
        </div>
         <div class="p-field">
          <label for="type">Account Type</label>
          <Dropdown
            v-model="state.type"
            id="type"
            :options="accountType"
            optionLabel="key"
          />
           <span v-if="v$.type.$error && submitted">
            <span
              id="email-error"
              v-for="(error, index) of v$.type.$errors"
              :key="index"
            >
              <small class="p-error">{{ error.$message }}</small>
            </span>
          </span>
          <small
            v-else-if="
              (v$.type.$invalid && submitted) || v$.type.$pending.$response
            "
            class="p-error"
            >{{ v$.type.required.$message.replace("Value", "Account Type") }}</small
          >
        </div>
         <div class="p-field" v-if="v$.nature.$model.key  == 'Expense'">
          <label for="expenseType">Expense Type</label>
          <Dropdown
            v-model="item.expenseType"
            id="expenseType"
            :options="expenseType"
            optionLabel="key"
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
            >Are you sure to delete <b>{{ state.accountName }}</b> ?</span
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
import { Options, Vue } from "vue-class-component";
import ChartService from "../../service/ChartService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, minLength,maxLength} from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: 'Chart of Accounts',
  components: {},
})
export default class ChartOfAccounts extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = '';
  private toast;
  private goToFirstLink = 0;
  private ChartService;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Chart of Accounts" , to: "chart-of-accounts"  },
  ];

  private item = {
    id: 0,
    expenseType: { key: "None" },
    status: "Active",
  };
  

  private state = reactive({
    accountName: "",
    code: "",
    nature: { key: "Assets" },
    type: { key: "Current" }
  });

  private validationRules = {
    accountName: {
      required,
    },
    nature: {
      required,
    },
    code: {
      required,
    },
    type: {
      required
    },
  };
  

  private v$ = useVuelidate(this.validationRules, this.state);

  private accountNature = [
    { key: "Assets" },
    { key: "Liability" },
    { key: "Equity" },
    { key: "Revenue" },
    { key: "Expense" }
  ];

  private accountType = [
    { key: "Current" },
    { key: "Non-Current" }
  ];

  private expenseType = [
    { key: "None" },
    { key: "Store Expense" },
    { key: "Bank Expense" }
  ];

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.ChartService = new ChartService();
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
    this.dialogTitle = "Add New Account";
    this.productDialog = true;
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
      if (this.item.id != 0) {
        this.ChartService
          .updateItem(
            this.item,
            this.state
          )
          .then((res) => {
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
      } else {
        this.ChartService
          .saveItem(
            this.item,
            this.state
          )
          .then((res) => {
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
    this.dialogTitle = "Update Account";
    this.productDialog = true;

    this.ChartService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.accountName      = res[0].account_name;
        this.state.code             = res[0].account_code;
        this.state.nature.key       = res[0].account_nature;
        this.state.type.key         = res[0].account_type;
        this.item.expenseType.key   = res[0].expense_type;
        this.item.status            = res[0].status;
        this.item.id                = res[0].id;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.accountName = data.account_name;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.ChartService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.ChartService.getItems(this.keyword,page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
    });
  }

  clearItem() {
    this.item = {
      id: 0,
      expenseType: { key: "None" },
      status: "Active",
    };
  
    this.state.accountName =  "";
    this.state.code =  "";
    this.state.nature = { key: "Assets" };
    this.state.type = { key: "Current" };
  }

  loadSearchData() {
    this.submitted = true;
    if (this.keyword) {
      this.goToFirstLink = 0;
      this.loadList(0);
    }
  }

  setExpenseType()
  {
    if(this.state.nature.key == 'Expense')
    {
      this.item.expenseType.key =  "Store Expense";
    }
  }
}
</script>