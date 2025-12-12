<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
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
          <Column field="branchName" header="Branch Name"></Column>
          <Column field="enable_notification" header="Enable Notification"></Column>
          <Column field="domain_name" header="Domain Name"></Column>
          <Column field="account_email" header="Account Email Address"></Column>
          <Column field="token_key" header="Token Key"></Column>
          <Column field="test_no" header="Test Mobile No"></Column>
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
          <label for="receiptHeading">Enable Notification</label>
          <Dropdown
            :options="notificationList"
            optionLabel="name"
            optionValue="name"
            v-model="v$.enableNotification.$model"
          />
          <small
            v-if="(v$.enableNotification.$invalid && submitted) || v$.enableNotification.$pending.$response"
            class="p-error"
            >{{
              v$.enableNotification.required.$message.replace("Value", "Enable Notification")
            }}</small
          >
        </div>
        <div class="p-field">
          <label for="selectedStore">Branch Name</label>
          <Dropdown
            :options="storeList"
            optionLabel="name"
            optionValue="id"
            v-model="item.selectedStore"
          />
        </div>
         <div class="p-field">
          <label for="domainName">Domain Name</label>
          <InputText
            id="domainName"
             v-model="v$.domainName.$model"
          />
          <small
            v-if="(v$.domainName.$invalid && submitted) || v$.domainName.$pending.$response"
            class="p-error"
            >{{
              v$.domainName.required.$message.replace("Value", "Domain Name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="accountEmailAddress"
            :class="{ 'p-error': v$.accountEmailAddress.$invalid && submitted }"
            >Account Email Address</label
          >
          <InputText
            id="accountEmailAddress"
            v-model="v$.accountEmailAddress.$model"
            :class="{ 'p-invalid': v$.accountEmailAddress.$invalid && submitted }"
          />
          <small
            v-if="(v$.accountEmailAddress.$invalid && submitted) || v$.accountEmailAddress.$pending.$response"
            class="p-error"
            >{{
              v$.accountEmailAddress.required.$message.replace("Value", "Account Email Address")
            }}</small
          >
        </div>
         <div class="p-field">
          <label
            for="tokenKey"
            :class="{ 'p-error': v$.tokenKey.$invalid && submitted }"
            >Token Key</label
          >
          <InputText
            id="tokenKey"
            v-model="v$.tokenKey.$model"
            :class="{ 'p-invalid': v$.tokenKey.$invalid && submitted }"
          />
          <small
            v-if="(v$.tokenKey.$invalid && submitted) || v$.tokenKey.$pending.$response"
            class="p-error"
            >{{
              v$.tokenKey.required.$message.replace("Value", "Token Key")
            }}</small
          >
        </div>
         <div class="p-field">
          <label
            for="testMobileNo"
            :class="{ 'p-error': v$.testMobileNo.$invalid && submitted }"
            >Test Mobile No</label
          >
          <InputText
            id="testMobileNo"
            v-model="v$.testMobileNo.$model"
            :class="{ 'p-invalid': v$.testMobileNo.$invalid && submitted }"
          />
          <small
            v-if="(v$.testMobileNo.$invalid && submitted) || v$.testMobileNo.$pending.$response"
            class="p-error"
            >{{
              v$.testMobileNo.required.$message.replace("Value", "Test Mobile No")
            }}</small
          >
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
            >Are you sure to delete <b>{{ item.receiptHeading }}</b> ?</span
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
import SmsService from "../../service/SmsService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required} from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  components: {},
})
export default class SmsSettings extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = '';
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private smsService;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private storeList = [];
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Sms Settings" },
  ];

  private item = {
    selectedStore: 0,
    id: 0,
    status: "Active",
  };
  

  private state = reactive({
    enableNotification: 'Enable',
    domainName: "",
    accountEmailAddress: "",
    tokenKey: "",
    testMobileNo: ""
  });

  private validationRules = {
    enableNotification: {
      required,
    },
    domainName: {
      required,
    },
    accountEmailAddress: {
      required,
    },
    tokenKey: {
      required,
    },
    testMobileNo: {
      required,
    }
  };

  private notificationList = [
    {
      name: 'Enable',
    },
    {
      name: 'Disable',
    }
  ];
  

  private v$ = useVuelidate(this.validationRules, this.state);

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.smsService = new SmsService();
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
    this.dialogTitle = "Add Settings";
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
        this.smsService
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
        this.smsService
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
    this.dialogTitle = "Update Settings";
    this.productDialog = true;

    this.smsService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.enableNotification       = res[0].enable_notification;
        this.state.domainName               = res[0].domain_name;
        this.state.accountEmailAddress      = res[0].account_email;
        this.state.tokenKey                 = res[0].token_key;
        this.state.testMobileNo             = res[0].test_no;
        this.item.status                    = res[0].status;
        this.item.selectedStore             = res[0].branch_id;
        this.item.id                        = res[0].id;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.smsService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.smsService.getItems(this.item.selectedStore,page).then((data) => {
      this.lists              = data.records;
      this.totalRecords       = data.totalRecords;
      this.storeList          = data.stores;
      this.limit              = data.limit;
      this.currentStoreID     = data.currentStoreID;
    });
  }

  clearItem() {
    
    this.state = {
      enableNotification: '',
      domainName: "",
      accountEmailAddress: "",
      tokenKey: "",
      testMobileNo: ""
    }; 
    
    this.item = {
      id: 0,
      selectedStore: 0,
      status: "Active",
    };
  }
}
</script>