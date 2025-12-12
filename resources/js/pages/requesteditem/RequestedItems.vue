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
          <Column field="item_name" header="Requested Item"></Column>
          <Column field="description" header="Description"></Column>
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
            for="requestedItem"
            :class="{ 'p-error': v$.requestedItem.$invalid && submitted }"
            >Item Name</label
          >
          <InputText
            id="requestedItem"
            v-model.trim="v$.requestedItem.$model"
            :class="{ 'p-invalid': v$.requestedItem.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="(v$.requestedItem.$invalid && submitted) || v$.requestedItem.$pending.$response"
            class="p-error"
            >{{
              v$.requestedItem.required.$message.replace("Value", "Requested Item")
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
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import RequestedItem from "../../service/RequestedItemService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required} from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: 'Requested Items',
  components: {},
})
export default class RequestedItems extends Vue {
  private lists = [];
  private dialogTitle;
  private keyword = '';
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
    id: 0
  };

  private item = {
    id: 0,
    description: "",
    status: "Active",
  };
  

  private state = reactive({
    requestedItem: "",
  });

  private validationRules = {
    requestedItem: {
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
    this.dialogTitle = "Add New Item";
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
        this.requestedItem
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
        this.requestedItem
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
    this.dialogTitle = "Update Item";
    this.productDialog = true;

    this.requestedItem.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.requestedItem       = res[0].item_name;
        this.item.status               = res[0].status;
        this.item.description          = res[0].description == null ? "" : res[0].description;
        this.item.id                   = res[0].id;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.requestedItem = data.item_name;
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
    this.requestedItem.getItems(this.selectedStore.id,this.keyword,page).then((data) => {
      this.lists              = data.records;
      this.totalRecords       = data.totalRecords;
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

    this.state.requestedItem =  "";
  }

  loadSearchData() {
    this.submitted = true;
    this.goToFirstLink = 0;
    this.loadList(0);
  }
}
</script>