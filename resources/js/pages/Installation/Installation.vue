<template>
  <div style="height: 0.2em">
    <ProgressBar
      v-if="showProgress"
      mode="indeterminate"
      style="height: 0.2em"
    />
  </div>
  <div class="bg">
    <div class="wrapper">
      <h3 class="p-pt-3 p-pl-3">  <i style="font-size: 1rem" class="pi pi-download"></i> Install Medix
      </h3>
       <small class="p-ml-3">Please fill out all the required fields to install the app.</small> <br />
       <small class="p-ml-3">You need internet connection to install this app if you are installing on Local Pc.</small>
      <form @submit.prevent="authenticate(!v$.$invalid)" class="p-fluid p-p-3">
        <div class="p-field">
          <label
            for="name"
            :class="{ 'p-error': v$.storeName.$invalid && submitted }"
            >Store name</label
          >
          <InputText
            id="storeName"
            v-model="v$.storeName.$model"
            :class="{ 'p-invalid': v$.storeName.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="
              (v$.storeName.$invalid && submitted) ||
              v$.storeName.$pending.$response
            "
            class="p-error"
            >{{
              v$.storeName.required.$message.replace("Value", "Store name")
            }}</small
          >
        </div>

        <div class="p-field">
          <label
            for="name"
            :class="{ 'p-error': v$.storeCode.$invalid && submitted }"
            >Store code</label
          >
          <InputText
            id="storeCode"
            v-model="v$.storeCode.$model"
            :class="{ 'p-invalid': v$.storeCode.$invalid && submitted }"
            placeholder="4 digit store code"
          />
          <small
            v-if="
              (v$.storeCode.$invalid && submitted) ||
              v$.storeCode.$pending.$response
            "
            class="p-error"
            >{{
              v$.storeCode.required.$message.replace("Value", "Store code")
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
          <label
            for="userName"
            :class="{ 'p-error': v$.userName.$invalid && submitted }"
            >User Name</label
          >
          <InputText
            id="userName"
            v-model="v$.userName.$model"
            :class="{ 'p-invalid': v$.userName.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.userName.$invalid && submitted) ||
              v$.userName.$pending.$response
            "
            class="p-error"
            >{{
              v$.userName.required.$message.replace("Value", "User name")
            }}</small
          >
        </div>

        <div class="p-field">
          <label
            for="userEmail"
            :class="{ 'p-error': v$.userEmail.$invalid && submitted }"
            >User Email</label
          >
          <InputText
            id="userEmail"
            v-model="v$.userEmail.$model"
            :class="{ 'p-invalid': v$.userEmail.$invalid && submitted }"
            autoFocus
          />
          <small
            v-if="
              (v$.userEmail.$invalid && submitted) ||
              v$.userEmail.$pending.$response
            "
            class="p-error"
            >{{
              v$.userEmail.required.$message.replace("Value", "User Email")
            }}</small
          >
        </div>

        <div class="p-field">
          <label
            for="password"
            :class="{ 'p-error': v$.password.$invalid && submitted }"
            >Password</label
          >
          <InputText
            id="password"
            v-model="v$.password.$model"
            :class="{ 'p-invalid': v$.password.$invalid && submitted }"
          />
          <small
            v-if="
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
            for="purchaseCode"
            :class="{ 'p-error': v$.purchaseCode.$invalid && submitted }"
            >Envato Purchase Key</label
          >
          <InputText
            id="purchaseCode"
            v-model="v$.purchaseCode.$model"
            :class="{ 'p-invalid': v$.purchaseCode.$invalid && submitted }"
          />
          <small
            v-if="
              (v$.purchaseCode.$invalid && submitted) ||
              v$.purchaseCode.$pending.$response
            "
            class="p-error"
            >{{
              v$.purchaseCode.required.$message.replace("Value", "Purchase Key")
            }}</small
          >
        </div>
        <div class="p-field">
            <div class="p-col-2">
                <Button class="p-button-success" label="Install"  @click.prevent="installApp(!v$.$invalid)"  icon="pi pi-download" />
            </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Options } from "vue-class-component";
import { reactive } from "vue";
import Toaster from "../../helpers/Toaster";
import UserAuthentication from "../../service/UserAuthentication";
import router from "../../router";
import { email, required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";

@Options({
  components: {},
  title: "User login",
})
export default class Login extends Vue {
  private showProgress = false;
  private toast;
  private submitted = false;
  private state = reactive({
    storeName: "",
    storeCode: "0001",
    address: "",
    userName: "",
    userEmail: "",
    password: "",
    //purchaseCode: "d5a574c7-8f44-4608-88b1-ea6396ca5c14"
    purchaseCode: ""
  });

  private rules = {
    userEmail: {
      required,
      email,
    },
    storeName: {
      required,
    },
    storeCode: {
      required,
    },
    address: {
      required,
    },
    userName: {
      required,
    },
    password: {
      required,
    },
    purchaseCode: {
      required,
    }
  };

  private v$ = useVuelidate(this.rules, this.state);

  created() {
    this.toast = new Toaster();
  }

  mounted()
  {
    this.checkNewInstallation();
  }

  installApp(isFormValid) {

    this.submitted = true;
    
    if (isFormValid) {

        const auth = new UserAuthentication();
        this.showProgress = true;

        auth.installApp(
          this.state
        ).then((res) => {
          if(res == 'exists')
          {
            this.toast.showInfo('Sorry app already installed');
          }
          else if(res == 'success')
          {
            this.clearItems();
            this.toast.showSuccess('App installed successfully');
            router.replace({ path: "/login", params: {} });
          }
          else if(res == 'failed')
          {
            this.toast.showError('Sorry cannot install the app');
          }
          else
          {
            this.toast.showError(res);
          }
          
          this.submitted = false;
          this.showProgress = false;
        });
    }
  }

  clearItems()
  {
    this.state.storeName = '';
    this.state.storeCode = '';
    this.state.address = '';
    this.state.userName = '';
    this.state.userEmail = '';
    this.state.password = '';
    this.state.purchaseCode = '';
  }

  checkNewInstallation()
  {
      const auth = new UserAuthentication();  
        
      auth.checkNewInstallation().then((res) => {
          if(res)
          {
            router.replace({ path: "/login", params: {} });     
          }
      });
  }
}
</script>

<style scoped>
.wrapper {
  width: 60%;
  margin: 0 auto;
}
</style>
