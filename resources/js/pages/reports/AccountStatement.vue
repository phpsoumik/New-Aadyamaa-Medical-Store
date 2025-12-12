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
          <h5>{{storeName}} Account Statement</h5>
          <p>{{resultTitle}}</p>
          <h6 class="p-text-center" v-if="accountHolder != ''"> 
            <i class="pi pi-users"></i>
            {{accountHolder}} ({{accountHolderPhone}})
          </h6>
          <h6 class="p-text-center" v-if="accountHolder != ''"> 
            {{storeName}}
          </h6>
      </div>
      <div class="p-mt-2">
        <table class="table table-bordered table-hover">
          <tr>
            <td colspan="9" class="p-text-right">
              <h6>  Before Statement Balance : {{currency}} {{checkForCredit(beforeBalance)}} </h6>
            </td>
          </tr>
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">SNO</th>
             <th class="p-p-1">DATE</th>
             <th class="p-p-1">TYPE</th>
             <th class="p-p-1">RECEIPT NO</th>
             <th class="p-p-1">SOURCE</th>
             <th class="p-p-1">DESCRIPTION</th>
             <th class="p-p-1">AMOUNT</th>
             <th class="p-p-1">EXP. ADJUSTMENT</th>

             <th class="p-p-1">PAYMENT</th>
             <th class="p-p-1">BALANCE</th>
           </tr>
           <template v-for="(l,i) in lists" :key="l">
            <tr >
              <td class="p-p-1">{{i+1}}</td>
              <td class="p-p-1">{{formatDateTime(l.date)}}</td>
              <td class="p-p-1">{{l.type}}</td>
              <td class="p-p-1">{{l.receiptNo}}</td>
              <td class="p-p-1">{{convertToFull(l.type)}}</td>
              <td class="p-p-1">{{l.description}}</td>
              <td class="p-p-1">{{formatAmount(l.amount)}} </td>
              <td class="p-p-1">{{formatAmount(l.expadjust)}} </td>

              <td class="p-p-1">{{formatAmount(l.payment)}} </td>
              <td class="p-p-1">{{currency}} {{checkForCredit(l.balance)}}</td>
            </tr>
            </template>
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
               @change="clearForFilters()"
              :options="filterDates"
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
        <div class="p-grid">
          <div class="p-col">
             <div class="p-field">
              <label for="type">Profile</label>
                <AutoComplete
                  :delay="1000"
                  :minLength="3"
                  @item-select="saveProfile($event)"
                  scrollHeight="500px"
                  v-model="searchFilters.customerName"
                  :suggestions="profilerList"
                  placeholder="Search Profile"
                  @complete="searchProfiler($event)"
                  :dropdown="false"
                >
                  <template #item="slotProps">
                    <div>
                      TITLE :
                      <b class="pull-right">
                        {{ slotProps.item.account_title.toUpperCase() }}
                      </b>
                    </div>
                    <div>
                      Email :
                      <span class="pull-right">
                        {{ slotProps.item.email_address }}
                      </span>
                    </div>
                    <div>
                      Contact :
                      <span class="pull-right">
                        {{ slotProps.item.contact_no }}
                      </span>
                    </div>
                    <div>
                      Account Type :
                      <span class="pull-right">
                        {{ slotProps.item.account_type }}
                      </span>
                    </div>
                  </template>
                </AutoComplete>
            </div>
        </div>
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
import ProfilerService from "../../service/ProfilerService.js";
import UtilityOptions from "../../mixins/UtilityOptions";
import AutoComplete from "primevue/autocomplete";


interface ITransaction {
  date: string;
  receiptNo: string;
  description: string;
  type: string;
  amount: number;
  payment: number;
  balance: number;
}


@Options({
  title: 'Account Statement',
  components: {AutoComplete},
})

export default class AccountStatement extends mixins(UtilityOptions) {
  private lists : ITransaction [] = [];
  private profilerList = [];
  private profilerService;
  private reportService;
  private resultTitle = "";
  private beforeBalance = 0;
  private accountHolderPhone = "";
  private accountHolder = "";
  private storeName = "";
  private productDialog = false;
  private loading = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Reports", to: "reports" },
    { label: "Account Statement" , to: "account-statement" },
  ];

  private totalBalance = 0;

  private searchFilters = {
    id: "",
    date1: "",
    date2: "",
    filterType: "None",
    customerName: "",
    storeID: 0,
    profileID: 0,
  };

  private reportTypes = [
    {
      name: 'Assets',
    },
    {
      name: 'Liability',
    },
    {
      name: 'Equity',
    },
    {
      name: 'Revenue',
    },
    {
      name: 'Expense',
    },
  ];

  private filterDates = [];
  private dialogTitle;
  private submitted = false;
  private filterBranch = [];

  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.reportService = new StoreReports();
    this.profilerService = new ProfilerService();
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

    this.reportService.accountStatement(this.searchFilters).then((res) => {
        const data = this.camelizeKeys(res);
        alert(JSON.stringify(data.record));
        this.resultTitle = data.resultTitle;
        this.storeName = data.storeName;
        this.lists = data.record;
        this.beforeBalance = data.beforeBalance;
        this.accountHolder = data.accountHolder;
        this.accountHolderPhone = data.accountHolderPhone;
        this.loading = false;

      });
    this.productDialog = false;
  }

  checkForCredit(a)
  {
    let b = '';

    if(a < 0)
    {
      b = '('+this.formatAmount(Math.abs(a))+')';
    }
    else
    {
      b = this.formatAmount(a);
    }

    return b;
  }


  searchProfiler(event) {
    setTimeout(() => {
      this.profilerService.searchProfiler(event.query.trim()).then((data) => {
        this.profilerList = data.records;
      });
    }, 200);
  }

  saveProfile(event) {
    const profileInfo = event.value;
    this.searchFilters.customerName = profileInfo.account_title;
    this.searchFilters.profileID = profileInfo.id;
  }

  clearForFilters() {
    this.searchFilters.date1 = "";
  }

  clearSearch() {
    this.searchFilters.filterType = "None";
  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>