<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb
            :home="home"
            :model="items"
            class="p-menuitem-text p-p-1"
          />
        </template>

        <template #end>
          <div class="p-inputgroup">
            <InputText
              v-model.trim="keyword"
              required="true"
              :class="{ 'p-invalid': submitted && !keyword }"
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
          :value="lists"
          :lazy="true"
          :paginator="checkPagination"
          :rows="limit"
          :loading="loading"
          :totalRecords="totalRecords"
          :scrollable="true"
          @page="onPage($event)"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <template #empty>
            <div class="p-text-center p-p-3">No records found</div>
          </template>
          <Column field="id" header="ID"></Column>
          <Column field="name" header="Service Name"></Column>
          <Column field="priority" header="Priority"></Column>
          <Column field="service_type" header="Service Type"></Column>
          <Column field="status" header="Status"></Column>
          <Column header="Image">
            <template #body="slotProps">
              <img
                :src="imagePath + slotProps.data.picture"
                :alt="slotProps.data.picture"
                class="product-image"
              />
            </template>
          </Column>
          <Column :exportable="false" header="Action">
            <template #body="slotProps">
              <Button
                icon="pi pi-pencil"
                class="p-button-rounded p-button-primary p-mr-2"
                @click="editIem(slotProps.data)"
              />
              <Button
                v-if="slotProps.data.status == 'Active'"
                icon="pi pi-eye-slash"
                class="p-button-rounded p-button-danger"
                @click="confirmChangeStatus(slotProps.data, 'Inactive')"
              />
              <Button
                v-else-if="slotProps.data.status == 'Inactive'"
                icon="pi pi-eye"
                class="p-button-rounded p-button-warning"
                @click="confirmChangeStatus(slotProps.data, 'Active')"
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
          <label for="name">Name</label>
          <InputText
            id="name"
            v-model.trim="product.name"
            required="true"
            autofocus
            :class="{ 'p-invalid': submitted && !product.name }"
          />
          <small class="p-invalid" v-if="submitted && !product.name"
            >Name is required.</small
          >
        </div>
        <div class="p-field">
          <label for="itemTypes">Service type</label>
          <Dropdown
            v-model="product.serviceType"
            :options="itemTypes"
            optionLabel="key"
          />
        </div>
        <div class="p-field">
          <label for="Priority">Priority</label>
          <InputNumber id="Priority" v-model="product.priority" integeronly />
        </div>
        <div class="p-field">
          <label for="Priority">Upload Icon Image</label>
          <FileUpload
            mode="basic"
            v-model="product.picture"
            accept="image/*"
            customUpload="true"
            chooseLabel="Upload"
          />
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click="saveItem"
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
          <span v-if="product"
            >Change the status of <b> {{ product.name }}</b> to
            {{ productStatus }}?
          </span>
        </div>
        <template #footer>
          <Button
            label="No"
            icon="pi pi-times"
            class="p-button-text"
            @click="statusDialog = false"
          />
          <Button
            label="Yes"
            icon="pi pi-check"
            class="p-button-text"
            @click="changeStatus"
          />
        </template>
      </Dialog>
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Service from "../../service/Service";
import Toaster from "../../helpers/Toaster";

@Options({
  components: {},
})
export default class Services extends Vue {
  private imagePath = "";
  private lists = [];
  private services;
  private productStatus = "";
  private keyword = "";
  private productDialog = false;
  private checkPagination = true;
  private statusDialog = false;
  private loading = false;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Services" },
  ];
  private toast;

  private itemTypes = [{ key: "Alteration" }, { key: "Non Alteration" }];

  private product = {
    id: 0,
    name: "",
    priority: 2,
    serviceType: { key: "Alteration"},
    status: "",
    picture: "",
  };

  private dialogTitle;
  private dialogCallback;
  private submitted = false;

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.services = new Service();
    this.toast = new Toaster();
    this.imagePath = this.services.getBaseURL() + "uploads/services/";
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.product = {
      id: 0,
      name: "",
      priority: 2,
      serviceType: { key: "Alteration"},
      status: "",
      picture: "",
    };

    this.submitted = false;
    this.dialogTitle = "Add New Service";
    this.productDialog = true;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(e) {
    e.preventDefault();
    this.checkPagination = true;

    this.submitted = true;
    if (this.product.name.trim()) {
      if (this.product.id != 0) {
        this.services.updateItem(this.product).then((res) => {
          this.loadList(0);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        this.services.saveItem(this.product).then((res) => {
          this.loadList(0);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      }

      this.productDialog = false;
      this.product = {
        id: 0,
        name: "",
        priority: 2,
        serviceType: { key: "Alteration"},
        status: "",
        picture: "",
      };
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Service";
    this.productDialog = true;
    this.checkPagination = true;

    this.services.getItem(data).then((res) => {
      if (res.length > 0) {
        this.product = {
          id: res[0].id,
          name: res[0].name,
          priority: res[0].priority,
          serviceType: { key: res[0].service_type} ,
          status: res[0].status,
          picture: res[0].picture,
        };
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data, status) {
    this.product = {
      id:           data.id,
      name:         data.name,
      priority:     data.priority,
      serviceType:  { key: data.serviceType },
      status:       data.status,
      picture:      data.picture,
    };
    this.productStatus = status;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.product.status = this.productStatus;
    this.services.changeStatus(this.product).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.loading = true;
    this.services.getItems(page).then((data) => {
      this.lists = data.service_list;
      this.totalRecords = data.total_service_count;
      this.limit = data.limit_per_page;
      this.loading = false;
    });
  }

  loadSearchData() {
    this.submitted = true;
    if (this.keyword) {
      this.services.getSearchedServices(this.keyword).then((data) => {
        this.lists = data.service_list;
        this.loading = false;
        this.checkPagination = data.pagination;
      });
    }
  }
}
</script>

<style scoped>
.product-image {
  width: 30px;
  height: auto;
}
</style>
