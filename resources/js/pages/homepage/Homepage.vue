<template>
  <section class="p-m-2">
      <div class="p-grid">
        <div class="p-col-12 p-mt-2">
          <div class="p-d-flex p-jc-between">
              <div class="store-name p-mt-1">
                <i  class="pi pi-home"></i>
                {{branchName}}
              </div>
              <div>
                <Dropdown
                  v-model="branchID"
                  :options="stores"
                  optionLabel="name"
                  optionValue="id"
                  placeholder="Select a Store"
                  @change="getHomepageDetails()"
                />
              </div>
          </div>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-sm-12 p-lg-2 p-md-2" style="padding-right:0 !important">
          <div class="card" >
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1"> Low Qty </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2"># {{dashInfo.shortageQty}}</h4>
                  <small class="tiles-desc">Items need to import</small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/shortage_qty.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
        <div class="p-sm-12 p-lg-2 p-md-2" style="padding-right:0 !important">
          <div class="card">
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1">Account Holders </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2"># {{dashInfo.accountHolders}}</h4>
                  <small class="tiles-desc">All profiles including customers</small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/account_holder.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
        <div class="p-sm-12 p-lg-2 p-md-2" style="padding-right:0 !important">
          <div class="card">
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1"> Monthly Expense </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2">{{currency}} {{formatAmount(dashInfo.monthlyExpense)}}</h4>
                  <small class="tiles-desc">Expenses via Expense Voucher</small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/expenses.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
        <div class="p-sm-12 p-lg-2 p-md-2" style="padding-right:0 !important">
          <div class="card">
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1"> Items Qty </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2"># {{dashInfo.itemsQty}}</h4>
                  <small class="tiles-desc">No of items in stock</small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/item_qty.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
        <div class="p-sm-12 p-lg-2 p-md-2" style="padding-right:0 !important">
          <div class="card">
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1"> Sales Today </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2">{{currency}} {{formatAmount(dashInfo.salesToday)}}</h4>
                  <small class="tiles-desc">Amount of pos sales </small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/sales.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
        <div class="p-sm-12 p-lg-2 p-md-2">
          <div class="card">
            <div class="card-body p-p-2">
              <div style="float: left; width: 60%">
                <label class="mb-1"> Sales Qty </label>
                <div class="">
                  <h4 class="mt-2 p-mb-2"># {{dashInfo.salesQty}}</h4>
                  <small class="tiles-desc">No of pos sales </small>
                </div>
              </div>
              <div style="float: left; width: 40%; text-align: center">
                <img
                  src="../../assets/menu-icons/dashboard/sales_qty.png"
                  class="dash-icons-style"
                  alt=""
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-col-12">
          <div class="card">
            <div class="card-header">
              <h5>
                ({{currency}}) Over All Revenue vs Expense vs Profit (Last 7 Months)
              </h5>
            </div>
            <div class="card-body p-p-1">
              <Chart
                type="line"
                :data="multiAxisData"
                :options="multiAxisOptions"
                :height="100"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-md-8 p-sm-12 p-lg-8 p-p-0">
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> SALES TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.salesToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.salesMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> RETURN TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.returnToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.returnMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> EXPENSE TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.expenseToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.monthlyExpense)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> PURCHASE TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.purchaseToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.purchaseMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> PURCHASE RETURN TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.purchaseReturnToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.purchaseReturnMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> TRANSFER TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.transferToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.transferMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> CASH IN TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.cashInToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.cashInMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
            <div class="p-md-6 p-sm-12 p-lg-6" style="float:left">
              <Card>
                  <template #title>
                     <small> CASH AT BANK TODAY </small>
                  </template>
                  <template #content>
                     <div class="p-d-flex p-jc-between card-style">
                        <div>{{currency}} {{formatAmount(dashInfo.cashAtBankToday)}}</div>
                        <div class="weekly">Month {{currency}} {{formatAmount(dashInfo.cashAtBankMonth)}}</div>
                    </div>
                  </template>
              </Card>
            </div>
        </div>
        <div class="p-md-4 p-sm-12 p-lg-4" style="padding:0.5rem 0.2rem !important">
          <label> <i class="pi pi-plus-circle"></i> Recently Added Items</label>
          <DataTable
              :value="recentlyItems"
              class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
            >
              <template #empty>
                <div class="p-text-center p-p-3">No records found</div>
              </template>
              <Column field="qty" header="Units"></Column>
              <Column field="productName" header="Item Name"></Column>
              <Column field="mrp" header="MRP"></Column>
            </DataTable>
        </div>
      </div>
      <div class="p-grid">
        <div class="p-md-4 p-lg-4 p-sm-12 chart-style" >
          <h5 class="p-p-0">({{currency}}) Top Brands Monthly</h5>
          <Chart
            type="bar"
            :data="brandChartData"
            :options="storeActivityChartOptions"
            :height="220"
          />
        </div>
        <div class="p-md-4 p-lg-4 p-sm-12 chart-style">
          <h5>({{currency}}) Top Customers Monthly</h5>
          <Chart
            type="bar"
            :data="customersChart"
            :options="storeActivityChartOptions"
            :height="220"
          />
        </div>
        <div class="p-md-4 p-lg-4 p-sm-12 chart-style">
            <h5>({{currency}}) Top Users Monthly</h5>
            <Chart
              type="bar"
              :data="usersChart"
              :options="storeActivityChartOptions"
              :height="220"
            />
        </div>
      </div>
  </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import HomepageService from "../../service/HomepageService";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import { camelCase } from "lodash";
import UtilityOptions from "../../mixins/UtilityOptions";

interface ChartTypes  {
  name: string;
  amount: number;
}

interface RevenueTypes  {
  totalExpense: number;
  totalRevenue: number;
  transMonth: string;
}

@Options({
  components: { },
  title: "Dashboard",
})
export default class Homepage extends mixins(UtilityOptions) {
  private stores = [];
  private branchName = "";
  private branchID = "";
 
  private dashInfo = {
    shortageQty: 0,
    accountHolders: 0,
    monthlyExpense: 0,
    itemsQty: 0,
    salesToday: 0,
    salesQty: 0,
    salesMonth: 0,
    returnToday:0,
    returnMonth:0,
    expenseToday:0,
    purchaseToday:0,
    purchaseMonth:0,
    purchaseReturnToday:0,
    purchaseReturnMonth:0,
    transferToday:0,
    transferMonth:0,
    cashInToday:0,
    cashInMonth:0,
    cashAtBankToday:0,
    cashAtBankMonth:0
  }

  private recentlyItems = [];
  private revenueExpense: RevenueTypes [] = [];
  private monthList = [];
  private topBrands: ChartTypes [] = [];
  private topUsers: ChartTypes [] = [];
  private topCustomers: ChartTypes [] = [];
  private homepageTxn;
  private toast;

  private multiAxisOptions = {
    responsive: true,
    tooltips: {
      mode: "index",
      titleFontSize: 30,
      bodyFontSize: 22,
      intersect: true,
    },
  };

  private storeActivityChartOptions = {
    responsive: true,
    tooltips: {
      mode: "index",
      titleFontSize: 30,
      bodyFontSize: 22,
      intersect: true,
    },
    scales: {
      // yAxes: [
      //   {
      //     type: "linear",
      //     display: false,
      //     position: "left",
      //     id: "y-axis-1",
      //   },
      // ],
    },
  };
 

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.homepageTxn = new HomepageService();
    this.toast = new Toaster();
  } 

  mounted() {
    this.getHomepageDetails();
  }

  camelizeKeys = (obj) => {
    if (Array.isArray(obj)) {
      return obj.map((v) => this.camelizeKeys(v));
    } else if (obj !== null && obj.constructor === Object) {
      return Object.keys(obj).reduce(
        (result, key) => ({
          ...result,
          [camelCase(key)]: this.camelizeKeys(obj[key]),
        }),
        {}
      );
    }
    return obj;
  };

  getHomepageDetails() {
    this.homepageTxn.getHompageDetails(this.branchID).then((res) => {
      const d = this.camelizeKeys(res);

      this.topBrands    = d.topBrandsAmount;
      this.topUsers     = d.topUsers;
      this.topCustomers = d.topCustomers;

      this.dashInfo.shortageQty         =  d.shortageQty;
      this.dashInfo.accountHolders      =  d.accountHolders;
      this.dashInfo.monthlyExpense      =  d.monthlyExpense;
      this.dashInfo.itemsQty            =  d.itemsQty;
      this.dashInfo.salesToday          =  d.salesToday;
      this.dashInfo.salesQty            =  d.salesQty;
      this.dashInfo.salesMonth          =  d.salesMonth;
      this.dashInfo.returnToday         =  d.returnToday;
      this.dashInfo.returnMonth         =  d.returnMonth;
      this.dashInfo.expenseToday        =  d.expenseToday;
      this.dashInfo.purchaseToday       =  d.purchaseReturnToday;
      this.dashInfo.purchaseMonth       =  d.purchaseMonth;
      this.dashInfo.purchaseReturnToday =  d.purchaseReturnToday;
      this.dashInfo.purchaseReturnMonth =  d.purchaseReturnMonth;
      this.dashInfo.transferToday       =  d.transferToday;
      this.dashInfo.transferMonth       =  d.transferMonth;
      this.dashInfo.cashInToday         =  d.cashInToday;
      this.dashInfo.cashInMonth         =  d.cashInMonth;
      this.dashInfo.cashAtBankToday     =  d.cashAtBankToday;
      this.dashInfo.cashAtBankMonth     =  d.cashAtBankMonth;
      this.recentlyItems                =  d.recentlyAdded;
      this.branchName                   = d.storeInfo.name;
      //this.branchID                     = d.storeInfo.id;

      this.monthList                    = d.lastMonths;
      this.revenueExpense               = d.revenueExpense;
      this.stores                     = d.stores;
    });
  }


  get getRevenueExpenseChart()
  {
    let monthName: string [] = [];
    let revenues: number []  = [];
    let expenses: number []  = [];
    let profit: number []  = [];

    this.monthList.map(m => {
      const s = moment(m,'MM').format('MMM');
      const i = this.revenueExpense.filter(n => Number(m) == Number(n.transMonth));

      if(i.length > 0)
      {
        const r = i[0].totalRevenue;
        const e = i[0].totalExpense;
        revenues.push(this.formatAmount(r));
        expenses.push(this.formatAmount(e));
        profit.push(this.formatAmount(r-e));
      }
      else
      {
        revenues.push(0);
        expenses.push(0);
        profit.push(0);
      }
      
      monthName.push(s);
    });

    return [monthName,revenues,expenses,profit];
  }

  get sortBand()
  {
    let names:string [] = [];
    let amount:number []  = [];

    if(this.topBrands != null)
    {
      const sorted = this.topBrands.slice(0);
        sorted.sort(function(a,b) {
          return a.amount - b.amount;
      });


      sorted.map(e => {
        amount.push(e.amount);
        names.push(e.name);
      });

      amount = amount.reverse().slice(0, 4);
      names = names.reverse().slice(0, 4);

    }

    return [names,amount]
  }
  
  get sortCustomers()
  {
    let names:string [] = [];
    let amount:number []  = [];

    if(this.topCustomers != null)
    {

    const sorted = this.topCustomers.slice(0);
      sorted.sort(function(a,b) {
        return a.amount - b.amount;
    });

    sorted.map(e => {
      amount.push(e.amount);
      names.push(e.name);
    });

      amount = amount.reverse().slice(0, 4);
      names = names.reverse().slice(0, 4);
    }
    return [names,amount]
  } 
  
  get sortUsers()
  {
    let names:string [] = [];
    let amount:number []  = [];

    if(this.topUsers  != null)
    {
      const sorted = this.topUsers.slice(0);
        sorted.sort(function(a,b) {
          return a.amount - b.amount;
      });

      sorted.map(e => {
        amount.push(e.amount);
        names.push(e.name);
      });

      amount = amount.reverse().slice(0, 4);
      names = names.reverse().slice(0, 4);

    }

     return [names,amount]
  }

  get brandChartData() {
    return {
      labels: this.sortBand[0],
      datasets: [
        {
          label: "Top Brands "+this.currency,
          backgroundColor: "#004C97",
          yAxisID: "y-axis-1",
          data: this.sortBand[1],
        },
      ],
    };
  }


  get customersChart() {
    return {
      labels: this.sortCustomers[0],
      datasets: [
        {
          label: "Top Customers "+this.currency,
          backgroundColor: "#28a745",
          yAxisID: "y-axis-1",
          data: this.sortCustomers[1],
        },
      ],
    };
  }
  
  get usersChart() {
    return {
      labels: this.sortUsers[0],
      datasets: [
        {
          label: "Top Users "+this.currency,
          backgroundColor: "#ffc107",
          yAxisID: "y-axis-1",
          data: this.sortUsers[1],
        },
      ],
    };
  }

  get multiAxisData() {
    return {
      labels: this.getRevenueExpenseChart[0],
      datasets: [
        {
          type: 'bar',
          label: "Revenues "+this.currency,
          backgroundColor: "#004C97",
          data: this.getRevenueExpenseChart[1],
        },
        {
          type: 'bar',
          label: "Expenses "+this.currency,
          backgroundColor: "#ffc107",
          data: this.getRevenueExpenseChart[2],
        },
        {
          type: 'line',
          label: "Profit "+this.currency,
          backgroundColor: "#20c997",
          data: this.getRevenueExpenseChart[3],
        },
      ],
    };
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style scoped>
.store-name
{
  font-size: 22px;
  color: #1a5692;
}

.weekly
{
  color: #ccc;
  font-size: 26px;
}

.chart-style
{
  padding: 0.8rem !important;
}
</style>
