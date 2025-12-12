<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-inputgroup">
            <InputText v-model.trim="keyword" placeholder="Store Code " />
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
          <Column field="name" header="Store Name"></Column>
          <Column field="code" header="Store Code"></Column>
          <Column field="address" header="Address"></Column>
          <Column field="description" header="Description"></Column>
          <Column field="license_no" header="License No"></Column>
          <Column field="email" header="Email Address"></Column>
          <Column field="contact" header="Contact No"></Column>
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
        :style="{ width: '70vw' }"
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
            for="name"
            :class="{ 'p-error': v$.name.$invalid && submitted }"
            >Store name</label
          >
          <InputText
            id="name"
            v-model="v$.name.$model"
            :class="{ 'p-invalid': v$.name.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="(v$.name.$invalid && submitted) || v$.name.$pending.$response"
            class="p-error"
            >{{
              v$.name.required.$message.replace("Value", "Store name")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="code"
            :class="{ 'p-error': v$.code.$invalid && submitted }"
            >Store code <small> (Must be unique) </small></label
          >
          <InputText
            id="code"
            v-model="v$.code.$model"
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
            >{{
              v$.code.required.$message.replace("Value", "Store code")
            }}</small
          >
        </div>
        <div class="p-field">
          <label
            for="address"
            :class="{ 'p-error': v$.address.$invalid && submitted }"
            >Address</label
          >
          <InputText
            id="address"
            v-model="v$.address.$model"
            :class="{ 'p-invalid': v$.address.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.address.$invalid && submitted) ||
              v$.address.$pending.$response
            "
            class="p-error"
            >{{
              v$.address.required.$message.replace("Value", "Address")
            }}</small
          >
        </div>
        <div class="p-field">
          <label for="description">Description</label>
          <InputText id="description" v-model="item.description" />
        </div>
        <div class="p-field">
          <label for="license_no">License No</label>
          <InputText id="license_no" v-model="item.licenseNo" />
        </div>
        <div class="p-field">
          <label for="email">Email Address</label>
          <InputText id="email" v-model="item.email" />
        </div>
        <div class="p-field">
          <label for="contact">Contact No</label>
          <InputText id="contact" v-model="item.contact" />
        </div>
        <div class="p-grid">
          <h5 class="p-mt-5">Tax Settings</h5>
          <div class="p-col-12">
            <p>
              <small
                >1:
                <i> Choose show to make tax visible for this store. </i></small
              >
              <br />
              <small
                >2:
                <i>
                  Wrtie name of the tax in tax name field and write a value for
                  this tax in value (%) field.
                </i></small
              >
              <br />
              <small
                >3:
                <i>
                  Choose required or optional if you want to apply this tax to
                  all or specific customers.
                </i></small
              >
              <br />
              <small>4: <i> Choose accounting head for this tax. </i></small>
              <br />
            </p>
          </div>
          <div class="p-col-12">
            <div class="p-inputgroup">
              <span class="p-inputgroup-addon">
                <label for="show1" class="p-px-1 p-mt-2">Show</label>
                <Checkbox
                  id="show1"
                  v-model="item.show1"
                  @click="setTaxesDefault('first')"
                  :binary="true"
                />
              </span>
              <InputText
                placeholder="Tax Name 1"
                style="width: 30%"
                v-model="item.taxName1"
              />
              <InputNumber
                mode="decimal"
                style="width: 30%"
                :useGrouping="false"
                :maxFractionDigits="2"
                :minFractionDigits="2"
                placeholder="Tax (%)"
                v-model="item.taxValue1"
              />
              <span class="p-inputgroup-addon">
                <label for="required_1" class="p-px-1 p-mt-2">Required</label>
                <RadioButton
                  id="required_1"
                  value="Required"
                  v-model="item.requiredOptional1"
                />
                <label for="optional_1" class="p-px-1 p-mt-2 p-ml-2"
                  >Optional</label
                >
                <RadioButton
                  id="optional_1"
                  value="Optional"
                  v-model="item.requiredOptional1"
                />
              </span>
              <Dropdown
                style="width: 20%"
                id="linkedAccount"
                v-model="item.link1"
                optionLabel="account_name"
                optionValue="id"
                :options="chartList"
                :filter="true"
              />
            </div>
          </div>
          <div class="p-col-12">
            <div class="p-inputgroup">
              <span class="p-inputgroup-addon">
                <label for="show2" class="p-px-1 p-mt-2">Show</label>
                <Checkbox
                  id="show2"
                  v-model="item.show2"
                  @click="setTaxesDefault('second')"
                  :binary="true"
                />
              </span>
              <InputText
                placeholder="Tax Name 2"
                style="width: 30%"
                v-model="item.taxName2"
              />
              <InputNumber
                mode="decimal"
                style="width: 30%"
                :useGrouping="false"
                :maxFractionDigits="2"
                :minFractionDigits="2"
                placeholder="Tax (%)"
                v-model="item.taxValue2"
              />
              <span class="p-inputgroup-addon">
                <label for="required_2" class="p-px-1 p-mt-2">Required</label>
                <RadioButton
                  id="required_2"
                  value="Required"
                  v-model="item.requiredOptional2"
                />
                <label for="optional_2" class="p-px-1 p-mt-2 p-ml-2"
                  >Optional</label
                >
                <RadioButton
                  id="optional_2"
                  value="Optional"
                  v-model="item.requiredOptional2"
                />
              </span>
              <Dropdown
                style="width: 20%"
                id="linkedAccount"
                v-model="item.link2"
                optionLabel="account_name"
                optionValue="id"
                :options="chartList"
              />
            </div>
          </div>
          <div class="p-col-12">
            <div class="p-inputgroup">
              <span class="p-inputgroup-addon">
                <label for="show3" class="p-px-1 p-mt-2">Show</label>
                <Checkbox
                  id="show3"
                  v-model="item.show3"
                  @click="setTaxesDefault('third')"
                  :binary="true"
                />
              </span>
              <InputText
                placeholder="Tax Name 3"
                style="width: 30%"
                v-model="item.taxName3"
              />
              <InputNumber
                mode="decimal"
                style="width: 30%"
                :useGrouping="false"
                :maxFractionDigits="2"
                :minFractionDigits="2"
                placeholder="Tax (%)"
                v-model="item.taxValue3"
              />
              <span class="p-inputgroup-addon">
                <label for="required_3" class="p-px-1 p-mt-2">Required</label>
                <RadioButton
                  id="required_3"
                  value="Required"
                  v-model="item.requiredOptional3"
                />
                <label for="optional_3" class="p-px-1 p-mt-2 p-ml-2"
                  >Optional</label
                >
                <RadioButton
                  id="optional_3"
                  value="Optional"
                  v-model="item.requiredOptional3"
                />
              </span>
              <Dropdown
                style="width: 20%"
                id="linkedAccount"
                v-model="item.link3"
                optionLabel="account_name"
                optionValue="id"
                :options="chartList"
              />
            </div>
          </div>
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
            >Are you sure to delete <b>{{ state.name }}</b> ?</span
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
import BranchService from "../../service/BranchService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, minLength, maxLength } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";

@Options({
  title: 'Stores',
  components: {},
})

export default class Branches extends Vue {
  private lists = [];
  private chartList = [
    {
      id: 0,
      account_name: "",
    },
  ];
  private dialogTitle;
  private keyword = "";
  private toast;
  private goToFirstLink = 0;
  private BranchService;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Stores" , to: "stores" },
  ];

  private item = {
    id: 0,
    description: "",
    licenseNo: "",
    email: "",
    contact: "",
    status: "Active",
    show1: true,
    taxName1: "",
    taxValue1: 0,
    link1: 0,
    requiredOptional1: "Required",
    show2: true,
    taxName2: "",
    taxValue2: 0,
    requiredOptional2: "Optional",
    link2: 0,
    show3: false,
    taxName3: "",
    taxValue3: 0,
    requiredOptional3: "Required",
    link3: 0,
  };

  private state = reactive({
    name: "",
    code: "",
    address: "",
  });

  private validationRules = {
    name: {
      required,
    },
    code: {
      required,
      minLength: minLength(4),
      maxLength: maxLength(4),
    },
    address: {
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
    this.BranchService = new BranchService();
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
    this.dialogTitle = "Add New Branch";
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
      //check taxes
      let t = this.checkTaxesStatus();

      if (t.t1 && t.t2 && t.t3) {
        if (this.item.id != 0) {
          this.BranchService.updateItem(
            this.item,
            this.state.name,
            this.state.code,
            this.state.address
          ).then((res) => {
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
        } else {
          this.BranchService.saveItem(
            this.item,
            this.state.name,
            this.state.code,
            this.state.address
          ).then((res) => {
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
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Branch";
    this.productDialog = true;

    this.BranchService.getItem(data).then((res) => {
      if (res.length > 0) {
        this.state.name = res[0].name;
        this.state.code = res[0].code;
        this.state.address = res[0].address;
        this.item.description =
          res[0].description == null ? "" : res[0].description;
        this.item.email = res[0].email == null ? "" : res[0].email;
        this.item.licenseNo =
          res[0].license_no == null ? "" : res[0].license_no;
        this.item.contact = res[0].contact == null ? "" : res[0].contact;
        this.item.status = res[0].status == null ? "" : res[0].status;
        this.item.id = res[0].id;

        this.item.taxName1 = res[0].tax_name_1 == null ? "" : res[0].tax_name_1;
        this.item.taxName2 = res[0].tax_name_2 == null ? "" : res[0].tax_name_2;
        this.item.taxName3 = res[0].tax_name_3 == null ? "" : res[0].tax_name_3;

        this.item.taxValue1 = Number(res[0].tax_value_1);
        this.item.taxValue2 = Number(res[0].tax_value_2);
        this.item.taxValue3 = Number(res[0].tax_value_3);

        this.item.show1 = res[0].show_1 == "true" ? true : false;
        this.item.show2 = res[0].show_2 == "true" ? true : false;
        this.item.show3 = res[0].show_3 == "true" ? true : false;

        this.item.requiredOptional1 = res[0].required_optional_1;
        this.item.requiredOptional2 = res[0].required_optional_2;
        this.item.requiredOptional3 = res[0].required_optional_3;

        this.item.link1 = res[0].link1;
        this.item.link2 = res[0].link2;
        this.item.link3 = res[0].link3;
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.name = data.name;
    this.statusDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.BranchService.changeStatus(this.item).then((res) => {
      this.loadList(0);
      //SHOW NOTIFICATION
      this.toast.handleResponse(res);
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.BranchService.getItems(this.keyword, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.chartList = data.chartList;

      this.item.link1 = this.chartList[0].id;
      this.item.link2 = this.chartList[0].id;
      this.item.link3 = this.chartList[0].id;
    });
  }

  clearItem() {
    this.item.id = 0;
    this.item.description = "";
    this.item.status = "Active";
    this.item.licenseNo = "";
    this.item.email = "";
    this.item.contact = "";

    this.state.name = "";
    this.state.code = "";
    this.state.address = "";
  }

  loadSearchData() {
    this.submitted = true;
    if (this.keyword) {
      this.goToFirstLink = 0;
      this.loadList(0);
    }
  }

  setTaxesDefault(taxNumber) {
    if (taxNumber == "first") {
      if (this.item.show1 == false) {
        this.item.taxName1 = "";
        this.item.taxValue1 = 0;
      }
    } else if (taxNumber == "second") {
      if (this.item.show2 == false) {
        this.item.taxName2 = "";
        this.item.taxValue2 = 0;
      }
    } else if (taxNumber == "third") {
      if (this.item.show3 == false) {
        this.item.taxName3 = "";
        this.item.taxValue3 = 0;
      }
    }
  }

  checkTaxesStatus() {
    let t1 = true;
    let t2 = true;
    let t3 = true;

    if (this.item.show1 && this.item.taxName1 == "") {
      t1 = false;
      this.toast.showError("Missing Tax 1 name");
    }

    if (this.item.show2 && this.item.taxName2 == "") {
      t2 = false;
      this.toast.showError("Missing Tax 2 name");
    }

    if (this.item.show3 && this.item.taxName3 == "") {
      t3 = false;
      this.toast.showError("Missing Tax 3 name");
    }

    return { t1: t1, t2: t2, t3: t3 };
  }
}
</script>