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
          <Column field="printer_name" header="Printer Name"></Column>
          <Column field="printer_type" header="Printer Type"></Column>
          <Column field="default_printer" header="Is Default"></Column>
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
            for="printerName"
            :class="{ 'p-error': v$.printerName.$invalid && submitted }"
            >Printer Name</label
          >
          <InputText
            id="printerName"
            v-model="v$.printerName.$model"
            :class="{ 'p-invalid': v$.printerName.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.printerName.$invalid && submitted) ||
              v$.printerName.$pending.$response
            "
            class="p-error"
            >{{
              v$.printerName.required.$message.replace("Value", "Printer Name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="printerType"
            :class="{ 'p-error': v$.printerType.$invalid && submitted }"
            >Printer Type</label
          >
          <Dropdown
            id="printerType"
            v-model="v$.printerType.$model"
            :options="printerTypes"
            optionLabel="key"
          />
          <small
            v-if="
              (v$.printerType.$invalid && submitted) ||
              v$.printerType.$pending.$response
            "
            class="p-error"
            >{{
              v$.printerType.required.$message.replace("Value", "Printer Type")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="defaultPrinter"
            :class="{ 'p-error': v$.defaultPrinter.$invalid && submitted }"
            >Default Printer</label
          >
          <Dropdown
            id="defaultPrinter"
            v-model="v$.defaultPrinter.$model"
            :options="defaultTypes"
            optionLabel="key"
          />
          <small
            v-if="
              (v$.defaultPrinter.$invalid && submitted) ||
              v$.defaultPrinter.$pending.$response
            "
            class="p-error"
            >{{
              v$.defaultPrinter.required.$message.replace(
                "Value",
                "Default Printer"
              )
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
            >Are you sure to delete <b>{{ state.printerName }}</b> ?</span
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
import PrinterService from "../../service/PrinterService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: 'Printer Setups',
  components: {},
})
export default class PrinterSetups extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = "";
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private printerService;
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
    { label: "Printers", to: "printer-setup" },
  ];

  private printerTypes = [{ key: "Regular" }];

  private defaultTypes = [{ key: "Yes" }, { key: "No" }];

  private selectedStore = {
    id: 0,
  };

  private item = {
    id: 0,
    status: "Active",
  };

  private state = reactive({
    printerName: "",
    printerType: { key: "Regular" },
    defaultPrinter: { key: "No" },
  });

  private validationRules = {
    printerName: {
      required,
    },
    printerType: {
      required,
    },
    defaultPrinter: {
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
    this.printerService = new PrinterService();
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
    this.dialogTitle = "Add New Printer";
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
        this.printerService.updateItem(this.item, this.state).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        this.printerService
          .saveItem(this.item, this.state, this.currentStoreID)
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
    this.dialogTitle = "Update Printer";
    this.productDialog = true;

    this.printerService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.printerName = res[0].printer_name;
        this.state.printerType.key = res[0].printer_type;
        this.state.defaultPrinter.key = res[0].default_printer;
        this.item.status = res[0].status;
        this.item.id = res[0].id;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.printerName = data.printer_name;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.printerService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.printerService
      .getItems(this.selectedStore.id, this.keyword, page)
      .then((data) => {
        this.lists = data.records;
        this.totalRecords = data.totalRecords;
        this.storeList = data.stores;
        this.limit = data.limit;
        this.currentStoreID = data.currentStoreID;
      });
  }

  clearItem() {
    this.item = {
      id: 0,
      status: "Active",
    };

    this.state.printerName = "";
    this.state.printerType.key = "Regular";
    this.state.defaultPrinter.key = "No";
  }
}
</script>