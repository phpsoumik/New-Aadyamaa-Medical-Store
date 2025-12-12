<template>
  <div class="app-container">
      <div class="jumbotron p-1 text-center mb-0"  >
        <h5> <i class="pi pi-chart-bar" ></i> Store Reports</h5>
        <h6 class="font-weight-bold">Get the complete reporting of store.</h6>
      </div>
    <section class="section-menu">
      <div class="col-md-12">
        <div class="row">
          <template v-for="item in reportList" :key="item">
            <router-link
              v-if="can(item.label) == true"
              :to="item.to"
              class="col-12 col-md-3 text-center hover-settings"
            >
              <img class="menu-img-settings" :src="item.image" />
              <h5 class="menu-link-settings">{{ item.label }}</h5>
              <p class="menu-link-detail-settings">
                {{ item.description }}
              </p>
            </router-link>
          </template>
        </div>
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { Options, mixins } from "vue-class-component";
import UtilityOptions from "../../mixins/UtilityOptions";

@Options({
  title: 'Report',
  components: {},
})

export default class Report extends mixins(UtilityOptions) {
  private home = { icon: "pi pi-home", to: "/" };
  private items = [{ label: "Report" }];
  private reportList = [
    {
      label: "User Report",
      image: require("@/assets/menu-icons/reports/user_report.png").default,
      to: "/store/user-report",
      description:"",
    },
    {
      label: "Sales/Refund Report",
      image: require("@/assets/menu-icons/reports/sales_refund_report.png").default,
      to: "/store/sales-refund-reports",
      description:"",
    },
    {
      label: "Challan Report",
      image: require("@/assets/menu-icons/reports/stock_transfer.png").default,
      to: "/store/transfer-reports",
      description:"",
    },
    {
      label: "Purchasing Report",
      image: require("@/assets/menu-icons/reports/purchasing.png").default,
      to: "/store/purchasing-report",
      description:"",
    },
   
    {
      label: "Performance Report",
      image: require("@/assets/menu-icons/reports/performance.png").default,
      to: "/store/performance-report",
      description:"",
    },
    {
      label: "General Journal",
      image: require("@/assets/menu-icons/reports/general.png").default,
      to: "/store/general-journal-report",
      description:"",
    },
    {
      label: "Income Statement",
      image: require("@/assets/menu-icons/reports/income.png").default,
      to: "/store/income-statement-report",
      description:"",
    },
    {
      label: "Trial Balance",
      image: require("@/assets/menu-icons/reports/trail_balance.png").default,
      to: "/store/trial-balance-report",
      description:"",
    },
    {
      label: "Ledger Statement",
      image: require("@/assets/menu-icons/reports/ledger.png").default,
      to: "/store/ledger-statement",
      description:"",
    },
    {
      label: "Tax Report",
      image: require("@/assets/menu-icons/reports/tax.png").default,
      to: "/store/tax-report",
      description:"",
    },
   
    {
      label: "Account Statement",
      image: require("@/assets/menu-icons/reports/account_statement.png").default,
      to: "/store/account-statement",
      description:"",
    },
     {
      label: "Stock Report",
      image: require("@/assets/menu-icons/reports/stock.png").default,
      to: "/store/stock-report",
      description:"",
    },
    {
      label: "Stock Alert Report",
      image: require("@/assets/menu-icons/reports/alert.png").default,
      to: "/store/stock-alert-report",
      description:"",
     },
    // {
    //   label: "Activity Report",
    //   image: require("@/assets/menu-icons/reports/activity.png").default,
    //   to: "/store/activity-report",
    //   description:"",
    // },
    {
      label: "Stock Expiry Report",
      image: require("@/assets/menu-icons/reports/expiry.png").default,
      to: "/store/expiry-report",
      description:"",
    },
     {
      label: "Bank Reconciliation",
      image: require("@/assets/menu-icons/reports/bank.png").default,
      to: "/store/exp-return-report",
      description:"",
    }
   
  ];
}
</script>

<style scoped>
.ini-toolbar {
  padding: 9px 0px;
}
.menu-img-settings {
  width: 80px;
  height: 80px;
  text-align: center;
  border-radius: 100%;
  border: 1px solid #ccc;
  padding: 10px;
  margin: 10px;
}

.menu-link-settings {
  font-size: 18px;
  color: #004c97;
}

.menu-link-detail-settings {
  color: #68686b;
}
.section-menu {
  background-color: #fff;
  padding: 0px;
}
.hover-settings {
  border-top: 1px solid #eee;
  border-right: 1px solid #eee;
}
.hover-settings:hover {
  cursor: pointer;
  color: #fff;
  text-decoration: none;
  transition: 1s;
  background-color: #f7f7f7;
}
</style>