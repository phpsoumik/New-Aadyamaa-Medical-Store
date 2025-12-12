<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb
            :home="home"
            :model="items"
            class="p-menuitem-text p-p-1"
          />
        </template>

        <template #end>
          <div class="p-mx-2">
            <Button
              icon="pi pi-search"
              class="p-button-success px-4"
              @click="openDialog"
            />
          </div>
          <div class="">
            <Button
              icon="pi pi-print"
              class="p-button-primary px-4"
              @click="printReceiptUtil()"
            />
          </div>
        </template>
      </Toolbar>
      <div class="m-2 mt-4 mb-4 p-text-center">
          <h5>{{storeName}} Statement of Income</h5>
          <p>{{resultTitle}}</p>
      </div>
      <div class="p-mt-2">
        <table class="table table-bordered table-hover">
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">REVENUE ACCOUNT TITLE</th>
             <th class="p-p-1">Amount</th>
           </tr>
            <tr v-for="s in allRevenueAccounts" :key="s">
              <td class="p-p-1">{{s.accountName}}</td>
              <td class="p-p-1">{{currency}}  {{formatAmount(s.totalRevenue)}}</td>
            </tr>
            <tr style="background-color:#ccc;">
              <td class="p-p-1">Total </td>
              <td class="p-p-1">{{currency}} {{formatAmount(sumRevenue)}}</td>
            </tr>
            <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">EXPENSE ACCOUNT TITLE</th>
             <th class="p-p-1">Amount</th>
           </tr>
            <tr v-for="s in allExpenseAccounts" :key="s">
              <td class="p-p-1">{{s.accountName}}</td>
              <td class="p-p-1"> {{currency}} {{formatAmount(s.totalExpense)}}</td>
            </tr>
            <tr style="background-color:#ccc;">
              <td class="p-p-1">Total </td>
              <td class="p-p-1">{{currency}} {{formatAmount(sumExpense)}}</td>
            </tr>
            <tr style="background-color:green; color:#fff;">
              <td class="p-p-1">Net Profit / Lost</td>
             <td class="p-p-1"> {{currency}} {{formatAmount(netProfit)}}</td>
            </tr>
        </table>
      </div>

      <Dialog
        v-model:visible="productDialog"
        :style="{ width: '50vw' }"
        :maximizable="true"
        position="top"
        class="p-fluid"
      >
        <template #header>
          <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
           <i class="pi pi-search" style="font-size:1.2rem;"></i> {{ dialogTitle }}
          </h5>
        </template>
        <div class="p-field">
            <label for="filterStore">Filter Date</label>
            <Dropdown
              v-model="searchFilters.filterType"
              :options="filterDates"
              @change="clearForFilters()"
              :filter="true"
              optionLabel="key"
              optionValue="value"
            />
        </div>
        <h5>OR</h5>
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="from">Date From</label>
              <input type="date" id="from"  v-model="searchFilters.date1" @change="clearSearch" class="form-control">
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label for="to">Date To</label>
              <input type="date" id="to"  v-model="searchFilters.date2" @change="clearSearch" class="form-control">
            </div>
          </div>
        </div>
        <div class="field">
          <label for="filterStore">Store Type</label>
          <Dropdown
            v-model="searchFilters.storeType"
            :options="storeType"
            :filter="true"
            optionLabel="name"
            optionValue="value"
          />
        </div>
        <div class="p-grid pt-3">
          <div class="p-col">
             <div class="p-field">
                <label for="filterStore">Branch</label>
                <Dropdown
                    v-model="searchFilters.storeID"
                    :options="filterBranch"
                    :filter="true"
                    optionLabel="name"
                    optionValue="id"
                />
              </div>
          </div>
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Search"
            icon="pi pi-search"
            class="p-button-primary"
            @click="loadList"
          />
        </template>
      </Dialog>
    </div>
  </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";

interface IList {
  accountName: string;
  accountNature: string;
  totalExpense: number;
  totalRevenue: number;
}

@Options({
  title: 'Statement of Income',
  components: {},
})

export default class IncomeStatement extends mixins(UtilityOptions) {
  private lists: IList []  = [];
  private reportService;
  private resultTitle = "";
  private storeName = "";
  private productDialog = false;
  private loading = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Reports", to: "reports" },
    { label: "Statement of Income", to: "income-statement-report" },
  ];

  private searchFilters = {
    id: "",
    date1: "",
    date2: "",
    filterType: "None",
    storeType: "all_stores",
    storeID: 0,
  };

  private storeType = [
    {
      name: 'Single Store',
      value:'single_store'
    },
    {
      name: 'All Stores',
      value:'all_stores'
    }
  ];

  private brands:any = [];
  private sectors:any = [];
  private categories:any = [];
  private productTypes:any = [];

  private filterDates = [];
  private dialogTitle;
  private submitted = false;
  private filterBranch = [];

  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.reportService = new StoreReports();
  }
  
   //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.storeList();
    this.loadList();
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {       
    this.submitted = false;
    this.dialogTitle = "Filter Report";
    this.productDialog = true;
  }

  storeList()
  {
    this.reportService.getFilterList().then((res) => {
      this.filterBranch  = res.stores;
      this.filterDates   = res.datesList;
    });
  }
 
  // USED TO GET SEARCHED ASSOCIATE
  loadList() {
    this.loading = true;
    this.reportService.incomeStatement(this.searchFilters).then((res) => {
        const data = this.camelizeKeys(res);
        this.resultTitle = data.resultTitle;
        this.storeName = data.storeName;
        this.lists = data.record;
        this.loading = false;
      });
    this.productDialog = false;
  }

  get allRevenueAccounts()
  {
    const a = this.lists.filter(e => e.accountNature == 'Revenue');
    return a;
  }
  
  get allExpenseAccounts()
  {
    const a = this.lists.filter(e => e.accountNature == 'Expense');
    return a;
  }

  get sumRevenue()
  {
    let total = 0;

    this.lists.forEach(e => {
      if(e.accountNature == 'Revenue')
      {
        total = total + Number(e.totalRevenue);
      }
    });
    return total;
  }
  
  get sumExpense()
  {
    let total = 0;

    this.lists.forEach(e => {
      if(e.accountNature == 'Expense')
      {
        total = total + Number(e.totalExpense);
      }
    });
    return total;
  } 
  
  get netProfit()
  {
    return this.sumRevenue - this.sumExpense;
  } 

  clearForFilters() {
    this.searchFilters.date1 = "";
    this.searchFilters.date2 = "";
  }

  clearSearch() {
    this.searchFilters.filterType = "None";
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>