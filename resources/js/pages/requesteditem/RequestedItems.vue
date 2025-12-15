<!-- @format -->

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
              style="width: 15rem"
              v-model="selectedStore"
              :options="storeList"
              optionLabel="name"
              @change="loadList(0)"
            />
          </div>
          <div class="p-inputgroup">
            <InputText v-model.trim="keyword" placeholder="Search" />
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
          <Column field="customer.name" header="Customer Name"></Column>
          <Column field="customer.phone" header="Customer Phone"></Column>
          <Column field="medicine_name" header="Medicine"></Column>
          <Column field="quantity" header="Qty"></Column>
          <Column field="order_date" header="Order Date"></Column>
          <Column field="advance_payment" header="Advance Payment"></Column>
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
        <!-- Customer Selection -->
        <div class="p-field">
          <label for="customer">Customer</label>
          <div class="p-inputgroup">
            <Dropdown
              id="customer"
              v-model="selectedCustomer"
              :options="customerList"
              optionLabel="name"
              placeholder="Select Customer"
              :filter="true"
              @change="onCustomerChange"
            />
            <Button
              icon="pi pi-plus"
              class="p-button-success"
              @click="openCustomerDialog"
              title="Add New Customer"
            />
          </div>
        </div>

        <!-- Customer Phone Display -->
        <div class="p-field" v-if="selectedCustomer && selectedCustomer.id">
          <label for="customerPhoneDisplay">Customer Phone</label>
          <InputText
            id="customerPhoneDisplay"
            v-model="customerPhone"
            placeholder="Customer Phone"
            readonly
            disabled
          />
        </div>



        <!-- Medicine -->
        <div class="p-field">
          <label
            for="medicine"
            :class="{ 'p-error': v$.medicine.$invalid && submitted }"
            >Medicine</label
          >
          <InputText
            id="medicine"
            v-model="state.medicine"
            placeholder="Enter medicine name"
            :class="{ 'p-invalid': v$.medicine.$invalid && submitted }"
          />
          <small v-if="v$.medicine.$invalid && submitted" class="p-error"
            >Medicine is required</small
          >
        </div>

        <!-- Quantity -->
        <div class="p-field">
          <label
            for="quantity"
            :class="{ 'p-error': v$.quantity.$invalid && submitted }"
            >Quantity</label
          >
          <InputNumber
            id="quantity"
            v-model="item.quantity"
            :min="1"
            :class="{ 'p-invalid': v$.quantity.$invalid && submitted }"
          />
          <small v-if="v$.quantity.$invalid && submitted" class="p-error"
            >Quantity is required</small
          >
        </div>

        <!-- Order Date -->
        <div class="p-field">
          <label
            for="orderDate"
            :class="{ 'p-error': v$.orderDate.$invalid && submitted }"
            >Order Date</label
          >
          <Calendar
            id="orderDate"
            v-model="item.order_date"
            dateFormat="yy-mm-dd"
            :class="{ 'p-invalid': v$.orderDate.$invalid && submitted }"
          />
          <small v-if="v$.orderDate.$invalid && submitted" class="p-error"
            >Order date is required</small
          >
        </div>

        <!-- Advance Payment -->
        <div class="p-field">
          <div class="p-field-checkbox">
            <Checkbox
              id="hasAdvance"
              v-model="item.has_advance"
              :binary="true"
            />
            <label for="hasAdvance">Has Advance Payment?</label>
          </div>
        </div>

        <div class="p-field" v-if="item.has_advance">
          <label for="advancePayment">Advance Payment (RS)</label>
          <InputNumber
            id="advancePayment"
            v-model="item.advance_payment"
            mode="currency"
            currency="INR"
            locale="en-IN"
            :min="0"
          />
        </div>
        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="p-button-text"
            @click="hideDialog"
          />
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
            >Are you sure to delete <b>{{ state.requestedItem }}</b> ?</span
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

      <!-- Customer Dialog -->
      <Dialog
        v-model:visible="customerDialog"
        :style="{ width: '500px' }"
        header="Add New Customer"
        class="p-fluid"
      >
        <div class="p-field">
          <label for="customerName">Customer Name</label>
          <InputText
            id="customerName"
            v-model="newCustomer.name"
            placeholder="Enter customer name"
            tabindex="1"
            required
          />
        </div>
        <div class="p-field">
          <label for="customerPhone">Phone Number</label>
          <InputText
            id="customerPhone"
            v-model="newCustomer.phone"
            placeholder="Enter phone number"
            tabindex="2"
            required
          />
        </div>
        <div class="p-field">
          <label for="customerEmail">Email Address</label>
          <InputText
            id="customerEmail"
            v-model="newCustomer.email"
            placeholder="Enter email address"
            tabindex="3"
          />
        </div>
        <div class="p-field">
          <label for="customerAddress">Address</label>
          <InputText
            id="customerAddress"
            v-model="newCustomer.address"
            placeholder="Enter address"
            tabindex="4"
          />
        </div>
        <div class="p-field">
          <label for="customerStatus">Status</label>
          <Dropdown
            id="customerStatus"
            v-model="newCustomer.status"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Select Status"
            tabindex="5"
          />
        </div>
        <template #footer>
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="p-button-text"
            @click="customerDialog = false"
          />
          <Button
            label="Save Customer"
            icon="pi pi-check"
            class="p-button-primary"
            @click="saveCustomer"
          />
        </template>
      </Dialog>
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import RequestedItem from "../../service/RequestedItemService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: "Requested Items",
  components: {},
})
export default class RequestedItems extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = "";
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private requestedItem;
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
    { label: "Requested Items", to: "requested-items" },
  ];

  private selectedStore = {
    id: 0,
  };

  private customerList = [];
  private selectedCustomer = null;
  private customerPhone = "";
  private medicineList = [];
  private selectedMedicine = null;
  private customerDialog = false;
  private newCustomer = {
    name: "",
    phone: "",
    email: "",
    address: "",
    status: "Active",
  };

  private statusOptions = [
    { label: "Active", value: "Active" },
    { label: "Delete", value: "Delete" },
  ];

  private item = {
    id: 0,
    customer_id: null,
    medicine_name: "",
    quantity: 1,
    order_date: new Date(),
    advance_payment: 0,
    has_advance: false,
    status: "Active",
    branch_id: 0,
  };

  private state = reactive({
    medicine: "",
    quantity: 1,
    orderDate: new Date(),
  });

  private validationRules = {
    medicine: {
      required,
    },
    quantity: {
      required,
    },
    orderDate: {
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
    this.requestedItem = new RequestedItem();
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
    this.dialogTitle = "Add New Medicine Request";
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

    // Set state values to item
    this.item.medicine_name = this.state.medicine;
    this.item.quantity = this.state.quantity;
    // Format date properly for backend
    const orderDate = new Date(this.state.orderDate);
    this.item.order_date = orderDate.toISOString().split('T')[0]; // YYYY-MM-DD format

    if (isFormValid) {
      if (this.item.id != 0) {
        this.requestedItem.updateItem(this.item).then((res) => {
          this.loadList(this.goToFirstLink);
          this.toast.handleResponse(res);
        });
      } else {
        this.item.branch_id = this.currentStoreID;
        this.requestedItem.saveItem(this.item).then((res) => {
          this.goToFirstLink = 0;
          this.loadList(this.goToFirstLink);
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
    this.dialogTitle = "Update Medicine Request";
    this.productDialog = true;

    this.requestedItem.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.medicine = res[0].medicine_name || res[0].item_name;
        this.state.quantity = res[0].quantity || 1;
        this.state.orderDate = res[0].order_date
          ? new Date(res[0].order_date)
          : new Date();
        this.item.customer_id = res[0].customer_id;
        this.item.advance_payment = res[0].advance_payment || 0;
        this.item.has_advance = res[0].has_advance || false;
        this.item.status = res[0].status;
        this.item.id = res[0].id;
        
        // Set selected customer and phone if customer_id exists
        if (res[0].customer_id) {
          const customer = this.customerList.find(c => c.id === res[0].customer_id);
          if (customer) {
            this.selectedCustomer = customer;
            this.customerPhone = customer.phone || "";
          }
        } else {
          this.selectedCustomer = null;
          this.customerPhone = "";
        }
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
    this.requestedItem.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.requestedItem
      .getItems(this.selectedStore.id, this.keyword, page)
      .then((data) => {
        this.lists = data.records;
        this.totalRecords = data.totalRecords;
        this.storeList = data.stores;
        this.customerList = data.customers;
        this.limit = data.limit;
        this.currentStoreID = data.currentStoreID;
      });
  }

  clearItem() {
    this.item = {
      id: 0,
      customer_id: null,
      medicine_name: "",
      quantity: 1,
      order_date: new Date(),
      advance_payment: 0,
      has_advance: false,
      status: "Active",
      branch_id: 0,
    };

    this.selectedCustomer = null;
    this.customerPhone = "";
    this.selectedMedicine = null;
    this.state.medicine = "";
    this.state.quantity = 1;
    this.state.orderDate = new Date();
  }

  // Customer change handler
  onCustomerChange() {
    if (this.selectedCustomer && (this.selectedCustomer as any).id) {
      this.item.customer_id = (this.selectedCustomer as any).id;
      this.customerPhone = (this.selectedCustomer as any).phone || "";
    } else {
      this.customerPhone = "";
    }
  }

  // Medicine search
  searchMedicine(event) {
    if (event.query.length >= 2) {
      this.requestedItem.searchMedicine(event.query).then((medicines) => {
        this.medicineList = medicines;
      });
    }
  }

  // Medicine select handler
  onMedicineSelect(event) {
    this.item.medicine_name = event.value.name;
    this.state.medicine = event.value.name;
  }

  // Open customer dialog
  openCustomerDialog() {
    this.newCustomer = {
      name: "",
      phone: "",
      email: "",
      address: "",
      status: "Active",
    };
    this.customerDialog = true;
  }

  // Add new phone
  addNewPhone() {
    // Allow manual phone entry
    this.item.customer_phone = "";
  }

  loadSearchData() {
    this.submitted = true;
    this.goToFirstLink = 0;
    this.loadList(0);
  }

  // Save new customer
  saveCustomer() {
    if (this.newCustomer.name && this.newCustomer.phone) {
      this.requestedItem.storeCustomer(this.newCustomer).then((res) => {
        this.toast.handleResponse(res);
        if (res.alert === "success") {
          // Add to customer list
          (this.customerList as any[]).push(res.customer);
          // Select the new customer
          this.selectedCustomer = res.customer;
          this.item.customer_id = res.customer.id;
          this.customerDialog = false;
        }
      });
    }
  }
}
</script>
