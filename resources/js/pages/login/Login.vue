<template>
  <div style="height: 0.2em">
    <ProgressBar
      v-if="showProgress"
      mode="indeterminate"
      style="height: 0.2em"
    />
  </div>
  <div class="wrapper">
    <div id="formContent">
      <img
        src="../../assets/images/logo.png"
        class="logo p-mt-5 p-mb-3"
        alt="app logo"
      />

      <form @submit.prevent="authenticate(!v$.$invalid)" class="p-fluid p-p-3">
        <div class="p-field">
          <div class="p-float-label">
            <InputText
              id="email"
              v-model="v$.email.$model"
              :class="{
                'p-invalid': v$.email.$invalid && submitted,
              }"
              aria-describedby="email-error"
            />
            <label
              for="email"
              :class="{
                'p-error': v$.email.$invalid && submitted,
              }"
              >User Email*</label
            >
          </div>
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
              (v$.email.$invalid && submitted) || v$.email.$pending.$response
            "
            class="p-error"
            >{{ v$.email.required.$message.replace("Value", "Email") }}</small
          >
        </div>
        <div class="p-field">
          <div class="p-float-label">
            <Password
              id="password"
              v-model="v$.password.$model"
              :class="{
                'p-invalid': v$.password.$invalid && submitted,
              }"
              :feedback="false"
            />
            <label
              for="password"
              :class="{
                'p-error': v$.password.$invalid && submitted,
              }"
              >Password*</label
            >
          </div>
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
          <Button
            type="submit"
            label="Sign In"
            icon="pi pi-unlock"
            class="p-button-primary p-button-raised p-button-rounded"
          />
        </div>
      </form>

      <div id="formFooter">
        <table class="table table-bordered">
          <tr>
            <td colspan="3">
              <small>App Version V4.1.0</small>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Options } from "vue-class-component";
import { reactive } from "vue";
import Toaster from "../../helpers/Toaster";
import { useStore, ActionTypes } from "../../store";
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
    email: "",
    password: "",
  });

  private rules = {
    email: {
      required,
      email,
    },
    password: {
      required,
    },
  };

  private v$ = useVuelidate(this.rules, this.state);

  created() {
    this.toast = new Toaster();
  }

  mounted() {
    //this.checkNewInstallation();
  }

  checkNewInstallation() {
    const auth = new UserAuthentication();

    auth.checkNewInstallation().then((res) => {
      if (!res) {
        router.replace({ path: "/install", params: {} });
      }
    });
  }

  authenticate(isFormValid) {
    this.submitted = true;

    if (isFormValid) {
      const auth = new UserAuthentication();
      const store = useStore();
      this.showProgress = true;

      auth
        .loginUser(this.state.email.trim(), this.state.password.trim(), "Web")
        .then((res) => {
          if (typeof res !== "undefined") {
            this.toast.showSuccess("Greetings " + res.userName);
            store.dispatch(ActionTypes.AUTH_REQUEST, res.token);
            store.dispatch(ActionTypes.GET_CURRENCY, res.currency);
            store.dispatch(ActionTypes.PERMISSION_LIST, res.permissionList);
            router.replace({ path: "/store/dashboard", params: {} });
          } else {
            this.state.email = "";
            this.state.password = "";
            this.submitted = false;
          }
        });
    }

    this.showProgress = false;
  }

  loadUser(role) {
    if (role == "admin") {
      this.state.email = "admin@spantiklab.com";
      this.state.password = "123456";
    }
  }
}
</script>

<style scoped>
.logo {
  width: 90px;
}

/* STRUCTURE */

.wrapper {
  height: 100vh;
  background-color: #18638a;
  display: flex;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  width: 100%;
  padding: 20px;
}

#formContent {
  -webkit-border-radius: 10px 10px 10px 10px;
  border-radius: 10px 10px 10px 10px;
  background: #fff;
  width: 90%;
  max-width: 450px;
  position: relative;
  padding: 0px;
  -webkit-box-shadow: 0 30px 60px 0 rgba(0, 0, 0, 0.3);
  box-shadow: 0 30px 60px 0 rgba(0, 0, 0, 0.3);
  text-align: center;
}

#formFooter {
  background-color: #f6f6f6;
  border-top: 1px solid #dce8f1;
  padding: 25px;
  text-align: center;
  -webkit-border-radius: 0 0 10px 10px;
  border-radius: 0 0 10px 10px;
}

.table td {
  vertical-align: middle;
  padding: 2px;
}

.p-field {
  height: 60px;
}
</style>
