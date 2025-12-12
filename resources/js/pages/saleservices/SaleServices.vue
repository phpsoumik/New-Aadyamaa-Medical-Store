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
                  :filter="true"
                  style="width:15rem"
                  v-model="selectedStore"
                  :options="storeList"
                  optionLabel="name"
                  @change="loadList(0)"
                />
             </div>  
          <div class="p-inputgroup">
           
            <InputText
              v-model.trim="keyword"
              placeholder="Search"
            />
            <Button
              icon="pi pi-search "
              class="p-button-primary"
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
          <Column field="service_name" header="Service Name"></Column>
          <Column field="description" header="Description"></Column>
          <Column field="charges" header="Fee/Charges"></Column>
          <Column field="account_code" header="Account Code"></Column>
          <Column field="account_name" header="Income Account"></Column>
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
            for="serviceName"
            :class="{ 'p-error': v$.serviceName.$invalid && submitted }"
            >Service Name</label
          >
          <InputText
            id="serviceName"
            v-model.trim="v$.serviceName.$model"
            :class="{ 'p-invalid': v$.serviceName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="(v$.serviceName.$invalid && submitted) || v$.serviceName.$pending.$response"
            class="p-error"
            >{{
              v$.serviceName.required.$message.replace("Value", "Service Name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="description"
            >Description</label
          >
          <InputText
            id="description"
            v-model.trim="item.description"
          />
        </div>
         <div class="p-field">
          <label
            for="charges"
            :class="{ 'p-error': v$.charges.$invalid && submitted }"
            >Charges</label
          >
          <InputNumber
            id="charges"
            v-model="v$.charges.$model"
            :class="{ 'p-invalid': v$.charges.$invalid && submitted }"
            mode="decimal"
            :minFractionDigits="2"
            :maxFractionDigits="2"

          />
          <small
            v-if="(v$.charges.$invalid && submitted) || v$.charges.$pending.$response"
            class="p-error"
            >{{
              v$.charges.required.$message.replace("Value", "Charges")
            }}</small
          >
        </div>
        <div class="p-field">
          <label for="incomeAccount">Income Account</label>
          <Dropdown
            v-model="state.incomeAccount"
            id="incomeAccount"
            :options="incomeAccountList"
            optionLabel="account_name"
          />
           <span v-if="v$.incomeAccount.$error && submitted">
            <span
              id="p-error"
              v-for="(error, index) of v$.incomeAccount.$errors"
              :key="index"
            >
              <small class="p-error">{{ error.$message }}</small>
            </span>
          </span>
          <small
            v-else-if="
              (v$.incomeAccount.$invalid && submitted) || v$.incomeAccount.$pending.$response
            "
            class="p-error"
            >{{ v$.incomeAccount.required.$message.replace("Value", "Income Account") }}</small
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
            >Are you sure to delete <b>{{ state.serviceName }}</b> ?</span
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
import SaleService from "../../service/SaleService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required} from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: 'Sale Services',
  components: {},
})
export default class SaleServices extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = '';
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private saleService;
  private incomeAccountList;
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
    { label: "Sale Services", to: "sale-services" },
  ];

  private selectedStore = {
    id: 0
  };

  private item = {
    id: 0,
    description: "",
    status: "Active",
  };
  

  private state = reactive({
    serviceName: "",
    charges: 0,
    incomeAccount: {},
  });

  private validationRules = {
    serviceName: {
      required,
    },
    charges: {
      required,
    },
    incomeAccount: {
      required,
    }
  };
  

  private v$ = useVuelidate(this.validationRules, this.state);

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.saleService = new SaleService();
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
    this.dialogTitle = "Add New Service";
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
        this.saleService
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
        this.saleService
          .saveItem(
            this.item,
            this.state,
            this.currentStoreID
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
    this.dialogTitle = "Update Service";
    this.productDialog = true;

    this.saleService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.serviceName         = res[0].service_name;
        this.state.charges             = res[0].charges;
        this.state.incomeAccount   = res[0].income_account;
        this.item.status               = res[0].status;
        this.item.description          = res[0].description == null ? "" : res[0].description;
        this.item.id                   = res[0].id;

         this.incomeAccountList.filter((elem) => {
          if (elem.id == res[0].income_account) {
            this.state.incomeAccount = elem;
          }
        });
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.serviceName = data.service_name;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.saleService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.saleService.getItems(this.selectedStore.id,this.keyword,page).then((data) => {
      this.lists              = data.records;
      this.totalRecords       = data.totalRecords;
      this.incomeAccountList  = data.incomeAccountList;
      this.storeList          = data.stores;
      this.limit              = data.limit;
      this.currentStoreID     = data.currentStoreID;
    });
  }

  clearItem() {

    this.item = {
      id: 0,
      description: "",
      status: "Active",
    };

    this.state.serviceName =  "";
    this.state.charges =  0;
    this.state.incomeAccount =  {};
  }

  loadSearchData() {
    this.submitted = true;
    this.goToFirstLink = 0;
    this.loadList(0);
  }
}
</script>