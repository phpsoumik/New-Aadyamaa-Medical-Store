<template>
  <Dialog
    v-model:visible="productDialog"
    :style="{ width: '50vw' }"
    :maximizable="true"
    position="top"
    class="p-fluid"
    :modal="true"
    :closable="true"
    @hide="closeDialog"
  >
    <template #header>
      <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-check-circle"></i> {{ dialogTitle }}
      </h5>
    </template>

    <div class="p-field">
      <label
        for="accountTitle"
        >Account Title</label
      >
      <InputText
        id="accountTitle"
        v-model.trim="item.accountTitle"
        autoFocus
      />
     
    </div>
    <div class="p-field">
      <label
        for="contactNo"
        >Contact No</label
      >
      <InputText
        id="contactNo"
        v-model.trim="item.contactNo"
      />
    
    </div>
    <!-- <div class="p-field">
      <label for="emailAddress">Email Address</label>
      <InputText id="emailAddress" v-model.trim="item.emailAddress" aria-describedby="email-error" />
    </div>
    <div class="p-field">
      <label for="nationalId">National ID</label>
      <InputText id="nationalId" v-model.trim="item.nationalId" />
    </div> -->
    <div class="p-field">
      <label for="address">Address</label>
      <InputText id="address" v-model.trim="item.address" />
    </div>
    <div class="p-field">
      <label for="accountType">Account Type</label>
      <Dropdown
        id="accountType"
        v-model="item.accountType"
        :options="profilerTypes"
        optionLabel="key"
      />
    </div>
    <!-- <div class="p-field">
      <label for="description">Description</label>
      <InputText id="description" v-model.trim="item.description" />
    </div> -->
    <template #footer v-if="!previewOnly">
      <Button
        type="submit"
        label="Save"
        icon="pi pi-check"
        class="p-button-primary"
        @click.prevent="saveItem(true)"
      />
    </template>
  </Dialog>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import { reactive } from "vue";
import ProfilerService from "../service/ProfilerService.js";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";

@Options({
  props: {
    profilerDetail: Object,
  },
  watch: {
    profilerDetail(obj) {
      if (obj.statusType == "New") {
        this.previewOnly = false;
        this.openDialog();
      } 
      else if (obj.statusType == "Update") {
        this.previewOnly = false;
        this.item.id = obj.profilerID;
        this.editIem();
      }
      else if (obj.statusType == "Preview") {
        this.previewOnly = true;
        this.item.id = obj.profilerID;
        this.editIem();
      } 
      else {
        this.dialogTitle = "";
      }
      
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
      this.currentUserID = obj.currentUserID;
    },
  },
  emits: ["updateProfilerStatus"],
})
export default class ProfilerDialog extends Vue {
  private toast;
  private submitted = false;
  private productDialog = false;
  private previewOnly = false;
  private dialogTitle = "";
  private profilerService;
  private currentUserID = 0;

  private item = {
    id: 0,
    accountTitle:"",
    emailAddress: "",
    nationalId: "",
    contactNo:"",
    address: "",
    description: "",
    accountType: { key: "Customer" },
    status: "Active",
  };

  private state = reactive({
    accountTitle: "",
    contactNo: "",
  });

  private validationRules = {
    accountTitle: {
      required,
    },
    contactNo: {
      required,
    },
  };

  // private v$ = useVuelidate(this.validationRules, this.state);

  private profilerTypes = [
    { key: "Customer" },
    { key: "Supplier" },
    { key: "Salesman" },
    { key: "Default Customer" },
  ];

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.profilerService = new ProfilerService();
    this.toast = new Toaster();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.submitted = false;
    this.productDialog = true;
    this.clearItems();
  }

  clearItems() {
    this.item = {
      id: 0,
      accountTitle:"",
      contactNo:"",
      emailAddress: "",
      nationalId: "",
      address: "",
      description: "",
      accountType: { key: "Customer" },
      status: "Active",
    };

    this.state.accountTitle = "";
    this.state.contactNo = "";
  }

  closeDialog() {
    this.$emit("updateProfilerStatus", ["",{}]);
    this.productDialog = false;
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {
      if (this.item.id != 0) {
        this.profilerService.updateItem(this.item, this.state).then((res) => {
          this.$emit("updateProfilerStatus", ["load",{}]);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      } else {
        alert("saving profile "+JSON.stringify(this.item));
        this.profilerService
          .saveItem(this.item, this.state, this.currentUserID)
          .then((res) => {

            this.$emit("updateProfilerStatus", ["load",res.profileDetail]);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
          });
      }

      this.productDialog = false;
      this.clearItems();
    }
  }

  //OPEN DIALOG BOX TO EDIT
  editIem() {
    this.submitted = false;
    this.dialogTitle = "Update Profile";
    this.productDialog = true;
    this.profilerService.getItem(this.item).then((res) => {
      if (res.length > 0) {
        this.state.accountTitle   = res[0].account_title;
        this.item.emailAddress    = res[0].email_address == null ? "" : res[0].email_address;
        this.state.contactNo      = res[0].contact_no == null ? "" : res[0].contact_no;
        this.item.nationalId      = res[0].national_id == null ? "" : res[0].national_id;
        this.item.address         = res[0].address == null ? "" : res[0].address;
        this.item.description     = res[0].description == null ? "" : res[0].description;
        this.item.accountType.key = res[0].account_type;
        this.item.status          = res[0].status;
      }
    });
  }
}
</script>