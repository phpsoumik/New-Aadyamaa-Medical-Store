
import { createRouter,createWebHashHistory } from "vue-router";
import AppShell from '@/pages/wrapper/AppShell.vue';
 import Homepage from '@/pages/homepage/Homepage.vue';
 import Login from '@/pages/login/Login.vue';
 import ForbiddenAccess from '@/pages/forbidden/ForbiddenAccess.vue';
 import Initialization from '@/pages/initialization/Initialization.vue';
 import Stocks from '@/pages/stocks/Stocks.vue';
 import OptionTags from '@/pages/optiontags/OptionTags.vue';
 import PaymentMethods from '@/pages/payment_method/PaymentMethods.vue';
 import Bank from '@/pages/bank/Bank.vue';
 import Banking from '@/pages/banking/Banking.vue';
 import Users from '@/pages/users/Users.vue';
 import Branches from '@/pages/branches/Branches.vue';
 import ChartOfAccounts from '@/pages/chartofaccounts/ChartOfAccounts.vue';
 import SaleServices from '@/pages/saleservices/SaleServices.vue';
 import PrinterSetups from '@/pages/printersetups/PrinterSetups.vue';
 import ReceiptPrinters from '@/pages/receiptprinter/ReceiptPrinters.vue';
 import RequestedItems from '@/pages/requesteditem/RequestedItems.vue';
 import Profilers from '@/pages/profilers/Profilers.vue';
 import PosReceipt from '@/pages/pos_receipt/PosReceipt.vue';
 import Installation from '@/pages/Installation/Installation.vue';
 import JournalVoucher from '@/pages/journal_voucher/JournalVoucher.vue';
 import OtherVoucher from '@/pages/other_voucher/OtherVoucher.vue';
 import UserBalance from '@/pages/user_balance/UserBalance.vue';
 import ImportStock from '@/pages/import_stock/ImportStock.vue';
 import OpenHead from '@/pages/open_head/OpenHead.vue';
 import Invoice from '@/pages/invoice/Invoice.vue';
 import SalesReceipt from '@/pages/sales_receipt/SalesReceipt.vue';
 import PosPurchase from '@/pages/pos_purchase/PosPurchase.vue';
 import { useStore } from "./store";

 import AppCheckoutShell from '@/pages/wrapper/AppCheckoutShell.vue';
 import TransactionReceipt from '@/pages/transaction/TransactionReceipt.vue';
 import Privileges from '@/pages/privileges/Privileges.vue';
 import Report from '@/pages/reports/Report.vue';
 import SalesRefundReport from '@/pages/reports/SalesRefundReport.vue';
 import TransferReport from '@/pages/reports/TransferReport.vue';
 import PurchasingReport from '@/pages/reports/PurchasingReport.vue';
 import PerformanceReport from '@/pages/reports/PerformanceReport.vue';
 import GeneralJournal from '@/pages/reports/GeneralJournal.vue';
 import IncomeStatement from '@/pages/reports/IncomeStatement.vue';
 import TrialBalance from '@/pages/reports/TrialBalance.vue';
 import LedgerStatement from '@/pages/reports/LedgerStatement.vue';
 import TaxReport from '@/pages/reports/TaxReport.vue';
 import BankReconciliation from '@/pages/reports/BankReconciliation.vue';
 import AccountStatement from '@/pages/reports/AccountStatement.vue';
 import SmsSettings from '@/pages/sms_settings/SmsSettings.vue';
 import UsersReport from '@/pages/reports/UsersReport.vue';
 import StockReport from '@/pages/reports/StockReport.vue';
 import ActivityReport from '@/pages/reports/ActivityReport.vue';
 import StockExpiryReport from '@/pages/reports/StockExpiryReport.vue';
 import StockAlertReport from '@/pages/reports/StockAlertReport.vue';
import ExpiryReturnReport from '@/pages/reports/ExpiryReturnReport.vue';
import SupplierRateComparison from '@/pages/reports/SupplierRateComparison.vue';
// THIS IS SOUMIK CODE - Non Moving Items & FMCG Report[28-11-2025]
import NonMovingItems from "@/pages/reports/NonMovingItems.vue";
// THIS IS SOUMIK CODE - Product Sale History[28-11-2025]
import ProductSaleHistory from "@/pages/reports/ProductSaleHistory.vue";

const ifAuthenticated = (to, __, next) => {
  const u = userPermission(to.name);
  const store = useStore();
  if (store.getters.isAuthenticated == '') next({ path: '/login' })
  else if (u == false)
  {
    next({ path: '/forbidden' })
  }
  else
  {
    next();
    document.title = to.name;
  }
}

const ifNotAuthenticated = (_, __, next) => {

  const store = useStore();
  if (store.getters.isAuthenticated == '') { 
    next();
    document.title = _.name;
  }
  else next({ path: '/store/dashboard' })

}

const routes = [
  { path: '/', redirect: "/login", beforeEnter: ifAuthenticated },
  { path: '/login', name: 'login', component: Login, beforeEnter: ifNotAuthenticated },
  { path: '/install', name: 'install', component: Installation},
  { path: '/forbidden', name: 'forbidden', component: ForbiddenAccess },
  { path: '/pos', name: 'Orders', component: PosReceipt, beforeEnter: ifAuthenticated},
  { path: '/purchasing', name: 'Purchasing', component: PosPurchase, beforeEnter: ifAuthenticated},
   {
    path: '/process',
    name: 'Transaction Receipt',
    component: AppCheckoutShell,
    beforeEnter: ifAuthenticated,
    children: [
       { path: 'transactions', name: 'Transaction Receipt', component: TransactionReceipt },
     ]
    },
  {
    path: '/store',
    name: 'app_shell',
    component: AppShell,
    beforeEnter: ifAuthenticated,
    children: [
      { path: 'initialization', name: 'Initialization', component: Initialization },
       { path: 'stores', name: 'Branches', component: Branches },
       { path: 'banks', name: 'Banks', component: Bank },
       { path: 'banking', name: 'Banking', component: Banking },
       { path: 'dashboard', name: 'Dashboard', component: Homepage },
       { path: 'sale-services', name: 'Sale Services', component: SaleServices },
       { path: 'stocks', name: 'Stocks', component: Stocks },
       { path: 'chart-of-accounts', name: 'Chart Of Accounts', component: ChartOfAccounts },
       { path: 'option-tag', name: 'Option Tags', component: OptionTags,beforeEnter: ifAuthenticated, },
       { path: 'payment-method', name: 'Payment Methods', component: PaymentMethods },
       { path: 'journal-voucher', name: 'Journal Voucher', component: JournalVoucher },
       { path: 'other-voucher', name: 'Other Voucher', component: OtherVoucher },
       { path: 'import-stock', name: 'Import Stock', component: ImportStock },
       { path: 'user-balance', name: 'User Balance', component: UserBalance },
       { path: 'open-head', name: 'Open Head', component: OpenHead },
       { path: 'invoice', name: 'Invoice', component: Invoice },
       { path: 'sales-receipt', name: 'Sales/Refund Receipt', component: SalesReceipt },
       { path: 'users', name: 'Users', component: Users },
       { path: 'privileges', name: 'Privileges', component: Privileges },
      { path: 'receipt-printer', name: 'Receipt Content', component: ReceiptPrinters},
      { path: 'sms-settings', name: 'Sms Settings', component: SmsSettings},
      { path: 'printer-setup', name: 'Printer Setup', component: PrinterSetups},
      { path: 'requested-items', name: 'Requested Items', component: RequestedItems},
       { path: 'profilers', name: 'Profilers', component: Profilers },
       { path: 'reports', name: 'Report', component: Report },
       { path: 'sales-refund-reports', name: 'Sales/Refund Report', component: SalesRefundReport },
       { path: 'transfer-reports', name: 'Transfer Report', component: TransferReport },
       { path: 'purchasing-report', name: 'Purchase/Return Report', component: PurchasingReport },
       { path: 'performance-report', name: 'Performance Report', component: PerformanceReport },
       { path: 'general-journal-report', name: 'General Journal', component: GeneralJournal },
       { path: 'income-statement-report', name: 'Income Statement', component: IncomeStatement },
       { path: 'trial-balance-report', name: 'Trial Balance', component: TrialBalance },
       { path: 'ledger-statement', name: 'Ledger Statement', component: LedgerStatement },
       { path: 'tax-report', name: 'Tax Report', component: TaxReport },
       { path: 'bank-reconciliation', name: 'Bank Reconciliation', component: BankReconciliation },
       { path: 'account-statement', name: 'Account Statement', component: AccountStatement },
       { path: 'user-report', name: 'User Report', component: UsersReport },
       { path: 'stock-report', name: 'StockReport', component: StockReport },
       { path: 'stock-alert-report', name: 'Stock Alert Report', component: StockAlertReport },
       { path: 'activity-report', name: 'Activity Report', component: ActivityReport },
       { path: 'expiry-report', name: 'Stock Expiry Report', component: StockExpiryReport },
       { path: 'expiry_report', name: 'Stock Expiry Report', component: StockExpiryReport },
       { path: 'exp-return-report', name: 'Expiry Return Report', component: ExpiryReturnReport },
       { path: 'supplier-rate-comparison', name: 'Supp.Rate Comparison Tool', component: SupplierRateComparison },
         // THIS IS SOUMIK CODE - Non Moving Items & FMCG route[28-11-2025]
      {
        path: "non-moving-items",
        name: "Non Moving Items & FMCG",
        component: NonMovingItems,
      },
      // THIS IS SOUMIK CODE - Product Sale History route[28-11-2025]
      {
        path: "product-sale-history",
        name: "Product Sale Based Order",
        component: ProductSaleHistory,
      },
    ]
  }
]



const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

const userPermission = (permission) => {

  const store = useStore();

  const pL =  JSON.parse(store.getters.gerUserPermissions);

  const f = pL.filter(e =>  e.name == permission);

  let res = false;

    if(f.length > 0)
    {
      res = true;
    }

    return res;
}


// const router = createRouter({
//   history: createWebHistory(''),
//   routes,
// });

export default router;