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
              v-model="item.optionType"
              :options="optionTypes"
              optionLabel="key"
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
          <Column field="option_name" header="Name"></Column>
          <Column field="description" header="Description"></Column>
          <Column field="option_type" header="Option type"></Column>
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
            for="optionName"
            :class="{ 'p-error': v$.optionName.$invalid && submitted }"
            >Option name</label
          >
          <InputText
            id="optionName"
            v-model="v$.optionName.$model"
            :class="{ 'p-invalid': v$.optionName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="
              (v$.optionName.$invalid && submitted) ||
              v$.optionName.$pending.$response
            "
            class="p-error"
            >{{
              v$.optionName.required.$message.replace("Value", "Option name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label for="optionTypes">Option type</label>
          <Dropdown
            v-model="item.optionType"
            :options="optionTypes"
            optionLabel="key"
          />
        </div>
        <div class="p-field">
          <label for="Description">Description</label>
          <InputText id="Description" v-model="item.description" />
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
            >Are you sure to delete <b>{{ state.optionName }}</b> ?</span
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
import OptionTag from "../../service/OptionTag.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { email, required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import UtilityOptions from "../../mixins/UtilityOptions";

@Options({
  title: 'Option Tags',
  components: {},
})
export default class OptionTags extends mixins(UtilityOptions) {
  private lists = [];
  private dialogTitle;
  private toast;
  private goToFirstLink = 0;
  private optionTag;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Option Tags", to: "option-tag" },
  ];

  private item = {
    id: 0,
    optionType: { key: "Brands" },
    description: "",
    status: "",
  };

  private state = reactive({
    optionName: "",
  });

  private validationRules = {
    optionName: {
      required,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  private optionTypes = [
    { key: "Brands" },
    { key: "Brand Sectors" },
    { key: "Units" },
    { key: "Products Type" },
    { key: "Vehicles" },
    { key: "Category" }
  ];

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.optionTag = new OptionTag();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.state.optionName = "";
    this.item = {
      id: 0,
      optionType: this.item.optionType,
      status: "Active",
      description: "",
    };

    this.submitted = false;
    this.dialogTitle = "Add New Option";
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
        this.optionTag
          .updateItem(this.item, this.state.optionName)
          .then((res) => {
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
      } else {
        this.optionTag
          .saveItem(this.item, this.state.optionName)
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
      this.item.status = "";
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Option";
    this.productDialog = true;

    this.optionTag.getItem(data).then((res) => {
      if (res.length > 0) {
        //FIND THE CATEGORY TYPE AND MAKE IT AS SELECTED IN EDIT DIALOG BOX.
        this.state.optionName = res[0].option_name;
        this.item.description =
          res[0].description == null ? "" : res[0].description;
        this.item.status = res[0].status;
        this.item.id = res[0].id;

        this.optionTypes.filter((elem) => {
          if (elem.key == res[0].option_type) {
            this.item.optionType = elem;
          }
        });
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.optionName = data.option_name;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.optionTag.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.optionTag.getItems(this.item.optionType.key, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
    });
  }
}
</script>