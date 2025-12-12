<template>
  <section class="top-menu-vue element-to-hide">
    <Menubar :model="menuItems" />
    <div style="height: 0.2em; margin: 0; background-color: #eee">
      <ProgressBar
        v-if="progressBar"
        mode="indeterminate"
        style="height: 0.2em"
      />
    </div>
  </section>
</template>

<script lang="ts">
import { Options, mixins } from "vue-class-component";
import TabMenu from "primevue/tabmenu";
import router from "../router";
import { ActionTypes } from "../store";
import Toaster from "../helpers/Toaster";
import UtilityOptions from "../mixins/UtilityOptions";


@Options({
  components: {
    TabMenu,
  },
})
export default class Header extends  mixins(UtilityOptions) {

  get menuItems ()
  {

    const newList = this.items.filter(e => {
      const p = this.can(e.name);
      if((e.name != '' && p == true) || e.name == 'Settings') return e;
    });

    newList.forEach(e => {
      if(e.items != undefined)
      {
         e.items.forEach((i,j) => {
          const p = this.can(i.name);
          if((i.name != '' && p == false) && i.name != 'Logout'){
            e.items.splice(j, 1);
          }
        });
      }
    });

    return newList;
  }

  private items = [
    {
      label: "Dashboard",
      name: "Dashboard",
      icon: "pi pi-fw pi-home",
      to: "/store/dashboard",
    },
    {
      label: "Sale",
      name: "Orders",
      icon: "pi pi-fw pi-shopping-cart",
      to: "/pos"
    },
    {
      label: "Banking",
      name:"Banking",
      icon: "pi pi-fw pi-check-circle",
      to: "/store/banking"
    },
    {
      label: "Purchases",
      name: "Purchasing",
      icon: "pi pi-fw pi-table",
      to: "/purchasing"
    },
    {
      label: "Transactions",
      name: "Transaction Receipt",
      icon: "pi pi-fw pi-window-maximize",
      to: "/process/transactions"
    },
    {
      label: "Settings",
      name: 'Settings',
      icon: "pi pi-fw pi-cog",
      items: [
        {
          label: "Initialization",
          name: "Initialization",
          icon: "pi pi-fw pi-plus-circle",
          to: "/store/initialization",
        },
        {
          label: "Reports",
          name: "Report",
          icon: "pi pi-fw pi-chart-bar",
          to: "/store/reports",
        },
        {
          label: "Logout",
          name: 'Logout',
          icon: "pi pi-fw pi-power-off",
          command: () => this.logOut(),
        },
      ],
    },
  ];

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

  get progressBar() {
    return this.store.getters.getProgressBar;
  }
}
</script>

<style >
.top-menu-vue {
  position: fixed;
  z-index: 999;
  top: 0;
  width: 100%;
}
.p-menubar {
  padding: 0;
  background-color: #fff;
  border-radius: 0px;
}
.p-menubar-root-list {
  margin: 0 auto !important;
  padding: 0 !important;
}

.p-menuitem-icon {
  color: #000 !important;
}

.p-menuitem-text {
  color: #000 !important;
}

.p-menuitem-link {
  background-color: #eee;
  border-radius: 0px;
  margin: 3px 2px;
  padding: 0.8rem !important;
}

@media print {
  .element-to-hide {
    display: none;
  }
}
</style>
