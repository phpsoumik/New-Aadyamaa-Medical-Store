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
          <Column field="card_name" header="Name"></Column>
          <!-- <Column field="card_charges" header="Card Expense Charges (%)"></Column> -->
          <Column field="bank_name.bank" header="Bank Name"></Column>
          <!-- <Column field="charge_customer" header="Charges Pay By"></Column> -->
          <Column field="description" header="Description"></Column>
          <Column field="status" header="Status"></Column>
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
          <h6 class="p-dialog-titlebar p-dialog-titlebar-icon">
            {{ dialogTitle }}
          </h6>
        </template>
        <div class="p-field">
          <label
            for="cardName"
            :class="{ 'p-error': v$.cardName.$invalid && submitted }"
            >Card Name</label
          >
          <InputText
            id="cardName"
            v-model="v$.cardName.$model"
            :class="{ 'p-invalid': v$.cardName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="
              (v$.cardName.$invalid && submitted) ||
              v$.cardName.$pending.$response
            "
            class="p-error"
            >{{
              v$.cardName.required.$message.replace("Value", "Card name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label for="optionTypes">Associated Bank</label>
          <Dropdown
            v-model="v$.bankID.$model"
            :options="bankList"
            optionLabel="bank"
            optionValue="id"
          />
           <small
            v-if="
              (v$.bankID.$invalid && submitted) ||
              v$.cardCharges.$pending.$response
            "
            class="p-error"
            >{{
              v$.bankID.required.$message.replace("Value", "Bank Name")
            }}</small
          >
        </div>
        <!-- <div class="p-field">
          <label
            for="cardCharges"
            :class="{ 'p-error': v$.cardCharges.$invalid && submitted }"
            >Card Expense Charges (%)</label
          >
          <InputNumber
            id="cardCharges"
            v-model="v$.cardCharges.$model"
            :class="{ 'p-invalid': v$.cardCharges.$invalid && submitted }"
            :minFractionDigits="2"
            :maxFractionDigits="2"
            :useGrouping="false"
          />
          <small
            v-if="
              (v$.cardCharges.$invalid && submitted) ||
              v$.cardCharges.$pending.$response
            "
            class="p-error"
            >{{
              v$.cardCharges.required.$message.replace("Value", "Card charges")
            }}</small
          >
        </div> -->
        <div class="p-field">
          <label for="description"> Description </label>
          <InputText
            id="description"
            v-model="item.description"
          />
        </div>
        <!-- <div class="p-field">
          <label for="description"> Who will pay for the card charges? </label>
          <SelectButton v-model="item.chargeCustomer" :options="chargeCustomerList" optionLabel="name" optionValue="name" />
        </div> -->
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
            >Are you sure to delete <b>{{ state.cardName }}</b> ?</span
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
import PaymentMethod from "../../service/PaymentMethod.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import {required, minValue} from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import SelectButton from 'primevue/selectbutton';


interface bankList{
  id: number;
  bank: string;
}

@Options({
  title: 'Payment Methods',
  components: {SelectButton},
})
export default class PaymentMethods extends Vue {
  private lists = [];
  private bankList: bankList [] = [];
  private dialogTitle;
  private toast;
  private goToFirstLink = 0;
  private paymentMethod;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Payment Methods", to: "payment-method" },
  ];

  private item = {
    id: 0,
    description: "",
    status: "",
    chargeCustomer: "Store",
  };

  private chargeCustomerList = [
    {
      name : 'Store',
    },
    {
      name : 'Customer',
    }
  ];

  private state = reactive({
    cardName: "",
    cardCharges: 0,
    bankID: 0,
  });

  private validationRules = {
    cardName: {
      required,
    },
    cardCharges: {
      required,
    },
    bankID: {
      required,
      minValue: minValue(1)
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
    this.paymentMethod = new PaymentMethod();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.state.cardName = "";
    this.state.cardCharges = 0;
    this.state.bankID = 0;

    this.item = {
      id: 0,
      status: "Active",
      chargeCustomer: "Store",
      description: "",
    };

    this.submitted = false;
    this.dialogTitle = "Add New Card";
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
        this.paymentMethod
          .updateItem(this.item, this.state)
          .then((res) => {
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
      } else {
        this.paymentMethod
          .saveItem(this.item, this.state)
          .then((res) => {
            this.goToFirstLink = 0;
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
      }

      this.productDialog = false;
      this.item.id = 0;
      this.item.description = "";
      this.item.chargeCustomer = "Store";
      this.item.status = "Active";

      this.state.cardName = "";
      this.state.cardCharges = 0;
      this.state.bankID = 0;
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Card";
    this.productDialog = true;

    this.paymentMethod.getItem(data).then((res) => {
      if (res.length > 0) {
        //FIND THE CATEGORY TYPE AND MAKE IT AS SELECTED IN EDIT DIALOG BOX.
        this.state.cardName       = res[0].card_name;
        this.state.cardCharges    = res[0].card_charges;
        this.state.bankID         = res[0].bank_id;
        this.item.description     = res[0].description == null ? "" : res[0].description;
        this.item.status          = res[0].status;
        this.item.id              = res[0].id;
        this.item.chargeCustomer  = res[0].charge_customer;
      }
    });
  }


  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.paymentMethod.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.paymentMethod.getItems(this.item, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.bankList = data.banks;
    });
  }

  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.cardName = data.card_name;
    this.statusDialog = true;
  }
}
</script>