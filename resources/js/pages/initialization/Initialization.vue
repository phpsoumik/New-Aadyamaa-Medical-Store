<template>
  <div class="app-container">
    <Toolbar>
      <template #start>
        <Breadcrumb
          :home="home"
          :model="items"
          class="p-menuitem-text"
        />
      </template>
    </Toolbar>
    <section class="section-menu">
      <div class="col-md-12">
        <div class="row">
         
          <template v-for="item in initializationList" :key="item">
            <router-link
              :to="item.to"
              v-if="can(item.label) == true"
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
  title: 'Initialization',
  components: {},
})

export default class Initialization extends mixins(UtilityOptions) {
  private home = { icon: "pi pi-home", to: "/" };
  private items = [{ label: "Initialization" }];
  private initializationList = [
    {
      label: "Branches",
      image: require("@/assets/menu-icons/initialization/branch.png").default,
      to: "/store/stores",
      description:
        "Add and Manage stores. You can add new store or edit existing store details . Plus add and setup store details and manage store taxes.",
    },
    {
      label: "Users",
      image: require("@/assets/menu-icons/initialization/users.png").default,
      to: "/store/users",
      description:
        "Add and Manage user accounts and there roles.",
    },
    {
      label: "Banks",
      image: require("@/assets/menu-icons/initialization/bank.png").default,
      to: "/store/banks",
      description:
        "Add and Manage store bank accounts.",
    },
    {
      label: "Banking",
      image: require("@/assets/menu-icons/initialization/banking.png").default,
      to: "/store/banking",
      description:
        "Make bank transactions like cheques, bank deposits, funds transfer, receive funds and manage expenses related to banking.",
    },
    {
      label: "Chart Of Accounts",
      image: require("@/assets/menu-icons/initialization/chart_of_accounts.png").default,
      to: "/store/chart-of-accounts",
      description:
        "Add and Manage accounting heads for the stores.",
    },
    // {
    //   label: "Sale Services",
    //   image: require("@/assets/menu-icons/initialization/sale_Services.png").default,
    //   to: "/store/sale-services",
    //   description:
    //     "Register and manage all the services offer by company.",
    // },
    {
      label: "Option Tags",
      image: require("@/assets/menu-icons/initialization/tags.png").default,
      to: "/store/option-tag",
      description:
        "Set the settings of the application like brands, sectors categories, units and other options available inside app.",
    },
    {
      label: "Payment Methods",
      image: require("@/assets/menu-icons/initialization/payment_method.png").default,
      to: "/store/payment-method",
      description:
        "Set colors, conditions and other tags like Male, Female, Red , Broken etc.You can create and manage existing tag..",
    },
    {
      label: "Journal Voucher",
      image: require("@/assets/menu-icons/initialization/voucher.png").default,
      to: "/store/journal-voucher",
      description:
        "Pass any accounting double entry transaction against any account holder for the business activity.",
    },
     {
      label: "Other Voucher",
      image: require("@/assets/menu-icons/initialization/voucher.png").default,
      to: "/store/other-voucher",
      description:
        "Pass Debit,Credit Or Expense Voucher.Use Debit voucher when receiving  cash from your customer,Credit when paying cash to your supplier, Expense when recording any cash expenses of the store.",
    },
    {
      label: "User Balance",
      image: require("@/assets/menu-icons/initialization/user_balance.png").default,
      to: "/store/user-balance",
      description:
        "Setup the opening user balances of the customers when importing customers into this app . You can track his/her previous balances.",
    },
    {
      label: "Open Head",
      image: require("@/assets/menu-icons/initialization/open_head.png").default,
      to: "/store/open-head",
      description:
        "Open previous accounting heads till to date balances of each account head.",
    },
    {
      label: "Invoice",
      image: require("@/assets/menu-icons/initialization/invoice.png").default,
      to: "/store/invoice",
      description:
        "Create and manage invoices offered by services.You can add receiving payments against invoices.",
    },
    {
      label: "Sales/Refund Receipt",
      image: require("@/assets/menu-icons/initialization/sales_refund.png").default,
      to: "/store/sales-receipt",
      description:
        "Create sales or refund receipt for the selling or refunding of the services you provided.",
    },
    {
      label: "Import Stock",
      image: require("@/assets/menu-icons/initialization/import_stock.png").default,
      to: "/store/import-stock",
      description:
        "Upload bulk stock using csv or add stock one by one by managing it before uploading into database.",
    },
    {
      label: "Stocks",
      image: require("@/assets/menu-icons/initialization/stock.png").default,
      to: "/store/stocks",
      description:
        "Preview uploaded stock and manage its qty and other details.",
    },
    {
      label: "Profilers",
      image: require("@/assets/menu-icons/initialization/customers.png").default,
      to: "/store/profilers",
      description:
        "Add and manage profiles of customers, supplier or salesmen's profile using inside app.",
    },
    {
      label: "Printer Setup",
      image: require("@/assets/menu-icons/initialization/printer.jpg").default,
      to: "/store/printer-setup",
      description:
        "Add or manage the thermal printers for receipt printing set printer as default for receipts.",
    },
    {
      label: "Receipt Content",
      image: require("@/assets/menu-icons/initialization/printer_receipt.jpg").default,
      to: "/store/receipt-printer",
      description:
        "Add extra content under the store receipt . You can add or delete content for the store receipt from here.",
    },
    {
      label: "Requested Items",
      image: require("@/assets/menu-icons/initialization/polling.png").default,
      to: "/store/requested-items",
      description:
        "Note down the requested items from customers to remember for later while importing stock from supplier. ",
    },
    {
      label: "Privileges",
      image: require("@/assets/menu-icons/initialization/permissions.png").default,
      to: "/store/privileges",
      description:
        "Set roles and permissions for users to access or restrict the features available inside the app.",
    }
  ];
}
</script>

<style scoped>
.menu-img-settings {
  width: 80px;
  height: 80px;
  text-align: center;
  border-radius: 100%;
  margin: 10px;
  padding: 10px;
  border: 2px solid #eee;
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