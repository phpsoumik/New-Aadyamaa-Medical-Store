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
              placeholder="User name"
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
          scrollDirection="horizontal"
          scrollHeight="flex"
          @page="onPage($event)"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <template #empty>
            <div class="p-text-center p-p-3">No records found</div>
          </template>
          <Column field="name" header="Name"></Column>
          <Column field="roleName" header="Role"></Column>
          <Column field="branchName" header="Branch"></Column>
          <Column field="email" header="Email Address"></Column>
          <Column field="description" header="Description"></Column>
          <Column field="contact" header="Contact"></Column>
          <Column field="address" header="Address"></Column>
          <Column :exportable="false" header="Action">
            <template #body="slotProps">
              <span class="p-buttonset">
                <Button
                  icon="pi pi-pencil"
                  class="p-button-primary p-button-rounded p-button-lg p-p-4 p-mt-1 p-mb-1"
                  @click="editIem(slotProps.data)"
                />
                <Button
                  icon="pi pi-key"
                  class="p-button-warning p-button-rounded p-button-lg p-p-4 p-mt-1 p-mb-1"
                  @click="changeUserPin(slotProps.data)"
                />
                <Button
                  icon="pi pi-image"
                  class="p-button-success p-button-rounded p-button-lg p-p-4 p-mt-1 p-mb-1"
                  @click="openFileUploader(slotProps.data)"
                />
                <Button
                  icon="pi pi-trash"
                  class="p-button-danger p-button-rounded p-button-lg p-p-4 p-mt-1 p-mb-1"
                  @click="confirmChangeStatus(slotProps.data)"
                />
              </span>
            </template>
          </Column>
        </DataTable>
      </div>

      <Dialog
        v-model:visible="productDialog"
        :style="{ width: '100vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid p-m-0 p-dialog-maximized"
      >
        <template #header>
          <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
            <i class="pi pi-plus-circle" style="fontSize: 1.5rem"></i>
            {{ dialogTitle }}
          </h4>
        </template>

        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="role">User Role</label>
              <Dropdown
                v-model="item.role"
                :options="userRoles"
                placeholder="User Role"
                :filter="true"
                optionLabel="name"
                optionValue="id"
              />
            </div>
            <div class="p-field">
              <label for="branchId">Branch</label>
              <Dropdown
                v-model="item.branchId"
                :filter="true"
                placeholder="Choose Branch"
                :options="storesList"
                optionLabel="name"
              />
            </div>
            <div class="p-field">
              <label
                for="name"
                :class="{ 'p-error': v$.name.$invalid && submitted }"
                >User Name</label
              >
              <InputText
                id="name"
                v-model="v$.name.$model"
                :class="{ 'p-invalid': v$.name.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.name.$invalid && submitted) || v$.name.$pending.$response
                "
                class="p-error"
                >{{ v$.name.required.$message.replace("Value", "Name") }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="email"
                :class="{ 'p-error': v$.email.$invalid && submitted }"
                >User Email</label
              >
              <InputText
                id="email"
                v-model="v$.email.$model"
                :class="{ 'p-invalid': v$.email.$invalid && submitted }"
                aria-describedby="email-error"
              />
              <span v-if="v$.email.$error && submitted">
                <span
                  id="email-error"
                  v-for="(error, index) of v$.email.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.email.$invalid && submitted) ||
                  v$.email.$pending.$response
                "
                class="p-error"
                >{{
                  v$.email.required.$message.replace("Value", "Email")
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label for="address">Address</label>
              <InputText id="address" v-model="item.address" />
            </div>
            <div class="p-field">
              <label for="contact">Contact No</label>
              <InputText id="contact" v-model="item.contact" />
            </div>
            <div class="p-field">
              <label for="description">Description</label>
              <InputText id="description" v-model="item.description" />
            </div>
            <div class="p-field">
              <label
                for="password"
                :class="{ 'p-error': v$.password.$invalid && submitted }"
                >Password</label
              >
              <Password
                id="password"
                v-model="v$.password.$model"
                :class="{ 'p-invalid': v$.password.$invalid && submitted }"
                :feedback="false"
              />
              <span v-if="v$.password.$error && submitted">
                <span
                  id="password"
                  v-for="(error, index) of v$.password.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.password.$invalid && submitted) ||
                  v$.password.$pending.$response
                "
                class="p-error"
                >{{
                  v$.password.required.$message.replace("Value", "Password")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="confirm_password"
                :class="{ 'p-error': v$.password.$invalid && submitted }"
                >Confirm Password</label
              >
              <Password
                id="confirm_password"
                v-model="v$.confirmPassword.$model"
                :class="{
                  'p-invalid': v$.confirmPassword.$invalid && submitted,
                }"
                :feedback="false"
              />
              <span v-if="v$.confirmPassword.$error && submitted">
                <span
                  id="confirm_password"
                  v-for="(error, index) of v$.confirmPassword.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.confirmPassword.$invalid && submitted) ||
                  v$.confirmPassword.$pending.$response
                "
                class="p-error"
                >{{
                  v$.confirmPassword.required.$message.replace(
                    "Value",
                    "Confirm Password"
                  )
                }}</small
              >
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
        v-model:visible="editProductDialog"
        :style="{ width: '100vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid p-m-0 p-dialog-maximized"
      >
        <template #header>
          <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
            <i class="pi pi-user-edit" style="fontSize: 1.5rem"></i>
            {{ dialogTitle }}
          </h4>
        </template>

        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="role">User Role</label>
              <Dropdown
                v-model="item.role"
                :options="userRoles"
                placeholder="User Role"
                :filter="true"
                optionLabel="name"
                optionValue="id"
              />
            </div>
            <div class="p-field">
              <label for="branchId">Branch</label>
              <Dropdown
                v-model="item.branchId"
                :filter="true"
                placeholder="Choose Branch"
                :options="storesList"
                optionLabel="name"
              />
            </div>
            <div class="p-field">
              <label
                for="name"
                :class="{ 'p-error': v2$.name.$invalid && submitted }"
                >User Name</label
              >
              <InputText
                id="name"
                v-model="v2$.name.$model"
                :class="{ 'p-invalid': v2$.name.$invalid && submitted }"
              />
              <small
                v-if="
                  (v2$.name.$invalid && submitted) ||
                  v2$.name.$pending.$response
                "
                class="p-error"
                >{{
                  v2$.name.required.$message.replace("Value", "Name")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="email"
                :class="{ 'p-error': v2$.email.$invalid && submitted }"
                >User Email</label
              >
              <InputText
                id="email"
                v-model="v2$.email.$model"
                :class="{ 'p-invalid': v2$.email.$invalid && submitted }"
                aria-describedby="email-error"
              />
              <span v-if="v2$.email.$error && submitted">
                <span
                  id="email-error"
                  v-for="(error, index) of v2$.email.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v2$.email.$invalid && submitted) ||
                  v2$.email.$pending.$response
                "
                class="p-error"
                >{{
                  v2$.email.required.$message.replace("Value", "Email")
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label for="address">Address</label>
              <InputText id="address" v-model="item.address" />
            </div>
            <div class="p-field">
              <label for="contact">Contact No</label>
              <InputText id="contact" v-model="item.contact" />
            </div>
            <div class="p-field">
              <label for="description">Description</label>
              <InputText id="description" v-model="item.description" />
            </div>
          </div>
        </div>
        <div class="p-grid">
          <div class="p-col">
            <div class="user-profile p-p-2">
              <h5>User Profile</h5>
              <img v-if="v2$.email.$model != ''" class="p-mt-3" :src="getImgUrl(item.image)" :alt="item.image" />
            </div>
          </div>
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="saveItem(!v2$.$invalid)"
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

      <Dialog
        v-model:visible="pinDialog"
        :style="{ width: '40vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid"
      >
        <template #header>
          <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
            <i class="pi pi-key" style="fontSize: 1.5rem"></i>
            Change User PIN
          </h4>
        </template>
        <div class="p-formgrid p-grid">
          <div class="p-field p-col">
            <div class="p-field">
              <label
                for="name"
                :class="{ 'p-error': v$.name.$invalid && submitted }"
                >User Name</label
              >
              <InputText
                id="name"
                v-model="v$.name.$model"
                :class="{ 'p-invalid': v$.name.$invalid && submitted }"
                readonly="true"
              />
              <small
                v-if="
                  (v$.name.$invalid && submitted) || v$.name.$pending.$response
                "
                class="p-error"
                >{{ v$.name.required.$message.replace("Value", "Name") }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="email"
                :class="{ 'p-error': v$.email.$invalid && submitted }"
                >User Email</label
              >
              <InputText
                id="email"
                v-model="v$.email.$model"
                :class="{ 'p-invalid': v$.email.$invalid && submitted }"
                aria-describedby="email-error"
                readonly="true"
              />
              <span v-if="v$.email.$error && submitted">
                <span
                  id="email-error"
                  v-for="(error, index) of v$.email.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.email.$invalid && submitted) ||
                  v$.email.$pending.$response
                "
                class="p-error"
                >{{
                  v$.email.required.$message.replace("Value", "Email")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="password"
                :class="{ 'p-error': v$.password.$invalid && submitted }"
                >Password</label
              >
              <Password
                id="password"
                v-model="v$.password.$model"
                :class="{ 'p-invalid': v$.password.$invalid && submitted }"
                :feedback="false"
              />
              <span v-if="v$.password.$error && submitted">
                <span
                  id="confirm_password"
                  v-for="(error, index) of v$.password.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.password.$invalid && submitted) ||
                  v$.password.$pending.$response
                "
                class="p-error"
                >{{
                  v$.password.required.$message.replace("Value", "Password")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="confirm_password"
                :class="{ 'p-error': v$.confirmPassword.$invalid && submitted }"
                >Confirm Password</label
              >
              <Password
                id="confirm_password"
                v-model="v$.confirmPassword.$model"
                :class="{
                  'p-invalid': v$.confirmPassword.$invalid && submitted,
                }"
                :feedback="false"
              />
              <span v-if="v$.confirmPassword.$error && submitted">
                <span
                  id="confirm_password"
                  v-for="(error, index) of v$.confirmPassword.$errors"
                  :key="index"
                >
                  <small class="p-error">{{ error.$message }}</small>
                </span>
              </span>
              <small
                v-else-if="
                  (v$.confirmPassword.$invalid && submitted) ||
                  v$.confirmPassword.$pending.$response
                "
                class="p-error"
                >{{
                  v$.confirmPassword.required.$message.replace(
                    "Value",
                    "Confirm Password"
                  )
                }}</small
              >
            </div>
            <div class="p-field p-row">
              <Button
                type="submit"
                label="Change PIN"
                @click.prevent="changeStorePin(!v$.$invalid)"
                class="p-button-raised p-button-primary"
              />
            </div>
          </div>
        </div>
      </Dialog>
       <FileUploader
        :uploaderDetail="{
          status: uploaderStatus,
          dialogTitle: 'Upload User Profile Image:',
          imageType: 'image/*',
        }"
        v-on:updateUploaderStatus="updateUploaderStatus"
      />
    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import UserService from "../../service/UserService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { email, required, minLength } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import FileUploader from "../../components/FileUploader.vue";

@Options({
   title: 'Users',
   components: {
    FileUploader,
  },
})
export default class Users extends Vue {
  private lists = [];
  private goToFirstLink = 0;
  private UserService;
  private keyword = "";
  private productDialog = false;
  private uploaderStatus = false;
  private pinDialog = false;
  private editProductDialog = false;
  private submitted = false;
  private statusDialog = false;
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };

  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Users", to: "users" },
  ];

  private toast;

  private item = {
    id: 0,
    branchId: "",
    role: "",
    address: "",
    contact: "",
    image: "default.jpg",
    description: "",
    status: "Active",
  };

  private state = reactive({
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
  });

  private stateUpdateUser = reactive({
    name: "",
    email: "",
  });

  private validationRules = {
    name: {
      required,
    },
    email: {
      required,
      email,
    },
    password: {
      minLength: minLength(6),
      required,
    },
    confirmPassword: {
      minLength: minLength(6),
      required,
    },
  };

  private editValidationRules = {
    name: {
      required,
    },
    email: {
      required,
      email,
    },
  };

  private v$ = useVuelidate(this.validationRules, this.state);
  private v2$ = useVuelidate(this.editValidationRules, this.stateUpdateUser);

  private userRoles;
  private storesList;
  private dialogTitle;
  private dialogCallback;

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.UserService = new UserService();
    this.toast = new Toaster();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
  
    this.clearItem();

    this.editProductDialog = false;
    this.submitted = false;
    this.dialogTitle = "Add New User";
    this.productDialog = true;
  }

  //CLOSE THE ITEM DAILOG BOX
  hideDialog() {
    this.productDialog = false;
    this.editProductDialog = false;
    this.submitted = false;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      if (this.item.id != 0) {
        this.UserService.updateItem(
          this.item,
          this.stateUpdateUser.name,
          this.stateUpdateUser.email
        ).then((res) => {
          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });

        this.productDialog = false;
        this.editProductDialog = false;
        this.clearItem();
      } else {
        if (this.state.password == this.state.confirmPassword) {
          this.UserService.saveItem(
            this.item,
            this.state.name,
            this.state.email,
            this.state.password
          ).then((res) => {
            this.goToFirstLink = 0;
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });

          this.productDialog = false;
          this.editProductDialog = false;
          this.clearItem();
        } else {
          this.toast.showError("Password & Confirm Password is not matching");
        }
      }
    }
  }

  changeStorePin(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      if (this.state.password == this.state.confirmPassword) {
        this.UserService.changePin(this.item, this.state.password).then(
          (res) => {
            this.loadList(this.goToFirstLink);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          }
        );

        this.pinDialog = false;
        this.clearItem();
      } else {
        this.toast.showError("Password & Confirm Password is not matching");
      }
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem(data) {

    this.submitted = false;
    this.dialogTitle = "Update User";
    this.productDialog = false;
    this.editProductDialog = true;

    this.UserService.getItem(data).then((res) => {
      if (res.length > 0) {
        //FIND THE CATEGORY TYPE AND MAKE IT AS SELECTED IN EDIT DIALOG BOX.
        this.item.branchId = res[0].branch_id;
        this.item.role = res[0].role;
        this.item.address = res[0].address == null ? "" : res[0].address;
        this.item.contact = res[0].contact == null ? "" : res[0].contact;
        this.item.image = res[0].image;
        this.item.description =
          res[0].description == null ? "" : res[0].description;
        this.item.status = res[0].status;
        this.item.id = res[0].id;
        this.stateUpdateUser.name = res[0].name;
        this.stateUpdateUser.email = res[0].email;

        this.item.role = res[0].role;

        this.storesList.filter((elem) => {
          if (elem.id == res[0].branch_id) {
            this.item.branchId = elem;
          }
        });
      }
    });
  }

  //OPEN DIALOG BOX FOR CONFIRMATION
  confirmChangeStatus(data) {
    this.item.id = data.id;
    this.state.name = data.name;
    this.statusDialog = true;
  }

  changeUserPin(data) {
    this.item.id = data.id;
    this.state.name = data.name;
    this.state.email = data.email;
    this.pinDialog = true;
  }

  //CHANGE THE STATUS AND SEND HTTP TO SERVER
  changeStatus() {
    this.statusDialog = false;
    this.item.status = "Delete";
    this.UserService.changeStatus(this.item).then((res) => {
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
    this.UserService.getItems(this.keyword, page).then((data) => {
      this.storesList = data.branches;
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.userRoles = data.userRoles;
      this.limit = data.limit;
    });
  }

  clearItem() {
    this.item = {
      id: 0,
      branchId: "",
      role: "",
      address: "",
      contact: "",
      image: "default.jpg",
      description: "",
      status: "Active",
    };

    this.stateUpdateUser.name = "";
    this.stateUpdateUser.email = "";

    this.state.name = "";
    this.state.email = "";
    this.state.password = "";
    this.state.confirmPassword = "";
  }

  loadSearchData() {
    this.submitted = true;
    if (this.keyword) {
      this.goToFirstLink = 0;
      this.loadList(0);
    }
  }
  
  openFileUploader(data)
  {
    this.item.id = data.id;
    this.uploaderStatus = true;
  }

  updateUploaderStatus(params)
  {
    this.uploaderStatus = false;
    if(params.length > 0)
    {
      this.UserService.uploadProfileImage(params[0],this.item).then((res) => {
        this.toast.handleResponse(res);
      });
    }
  }

  getImgUrl(pic) {
    let userImg = ''
    try
    {
      userImg = require('@/assets/users/'+pic).default;
    }
    catch (error)
    {
      userImg = require('@/assets/users/default.jpg').default;
    }
    return userImg;
  }
}
</script>

<style scoped>
.user-profile img
{
  width: 150px;
  height: auto;
  border: 3px solid #ccc;
  border-radius: 5px;
}
</style>
