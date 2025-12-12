<template>
  <div class="wrapper p-text-center">
    <h2 class="p-text-center p-pt-5">Forbidden Access 403</h2>
    <h4 class="p-text-center">You are not authorized to access this page</h4>
    <Button label="Logout" icon="pi pi-fw pi-power-off" class="p-button-success " @click="logOut()" />
  </div>
</template>

<script lang="ts">
import { Options, mixins } from "vue-class-component";
import { ActionTypes } from "../../store";
import Toaster from "../../helpers/Toaster";
import router from "../../router";
import UtilityOptions from "../../mixins/UtilityOptions";

@Options({
  components: {},
  title: "Forbidden Access",
})
export default class ForbiddenAccess extends  mixins(UtilityOptions) {

  logOut() {
    const toast = new Toaster();

    const res = {
      alert: "info",
      msg: "Sign out successfully",
    };

    toast.handleResponse(res);

    this.store.dispatch(ActionTypes.AUTH_LOGOUT, "");

    router.push({ path: "/login" });
  }
 
}
</script>

<style scoped>
.wrapper
{
  background-color: #f7f7f7;
  height: 100vh;
}
</style>
