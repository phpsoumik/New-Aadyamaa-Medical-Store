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
          <h5>{{storeName}} General Ledger</h5>
          <p>{{resultTitle}}</p>
      </div>
      <div class="p-mt-2">
        <h5 class="p-text-center">{{searchFilters.reportType}}</h5>
        <table class="table table-bordered table-hover">
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">DATE</th>
             <th class="p-p-1">ACCOUNT TITLE AND EXPLANATION</th>
             <th class="p-p-1">SOURCE</th>
             <th class="p-p-1">DEBIT</th>
             <th class="p-p-1">CREDIT</th>
             <th class="p-p-1">BALANCE</th>
           </tr>
           <template v-for="t in ledgerTransactions" :key="t">
            <tr style="background-color:#ccc;">
              <td class="p-p-1" colspan="6">{{t.accountTitle}} ({{t.accountCode}})</td>
            </tr> 
            <tr>
              <td class="p-p-1">Previous From {{previousDate}} </td>
              <td class="p-p-1"></td>
              <td class="p-p-1"></td>
              <td class="p-p-1">{{formatAmount(previousDebit(t.accountID))}} </td>
              <td class="p-p-1">{{formatAmount(previousCredit(t.accountID))}} </td>
              <td class="p-p-1">{{checkForCredit(previousBalance(t.accountID))}}</td>
            </tr> 
            <tr v-for="l in t.transactions" :key="l">
              <td class="p-p-1">{{formatDate(l.date)}} / {{formatTime(l.date)}}</td>
              <td class="p-p-1">{{l.narration}}</td>
              <td class="p-p-1">{{l.source}}</td>
              <td class="p-p-1">{{formatAmount(l.debit)}} </td>
              <td class="p-p-1">{{formatAmount(l.credit)}} </td>
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
        <div class="p-grid">
          <div class="p-col">
            <div class="p-field">
              <label for="filterStore">Filter Type</label>
              <Dropdown
                v-model="searchFilters.reportType"
                :options="reportTypes"
                :filter="true"
                optionLabel="name"
                optionValue="name"
              />
          </div>
        </div>
        <div class="p-col">
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
          </div>
          <div class="p-col-12">
             <div class="p-field pt-3">
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
import AutoComplete from "primevue/autocomplete";

interface IList {
  accountCode: string;
  accountId: number;
  accountName: string;
  amount: number;
  createdAt: string;
  generatedSource: string;
  id: number;
  narration: string;
  transactionId: number;
  type: string;
  updatedAt:string;
}

interface ITransaction {
  date: string;
  narration: string;
  source: string;
  debit: number;
  credit: number;
  balance: number;
}

interface Ledger {
  accountTitle:string,
  accountID:number,
  accountCode:string,
  transactions: ITransaction []
}

interface PreviousTransaction {
  accountId:number;
  totalDebit:number;
  totalCredit: number;
}

@Options({
  title: 'General Ledger',
  components: {AutoComplete},
})

export default class LedgerStatement extends mixins(UtilityOptions) {
  private lists : IList [] = [];
  private previousList : PreviousTransaction [] = [];
  private reportService;
  private resultTitle = "";
  private previousDate = "";
  private storeName = "";
  private productDialog = false;
  private loading = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Reports", to: "reports" },
    { label: "General Ledger", to: "ledger-statement" },
  ];

  private searchFilters = {
    id: "",
    date1: "",
    date2: "",
    filterType: "None",
    reportType: "Assets",
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
    this.reportService.ledgerStatement(this.searchFilters).then((res) => {
        const data = this.camelizeKeys(res);
        this.resultTitle = data.resultTitle;
        this.previousDate = data.previousDate;
        this.storeName = data.storeName;
        this.lists = data.record;
        this.previousList = data.previousRecord;
        this.loading = false;
      });
    this.productDialog = false;
  }

   get filterAccounts() : IList []  {
    let copyList :IList [] = [];

    if (this.lists != null) {
      copyList = [...this.lists];
    }

    const uniqueElementsBy = (arr, fn) =>
      arr.reduce((acc, v) => {
        if (!acc.some((x) => fn(v, x))) acc.push(v);
        return acc;
      }, []);

    const list = uniqueElementsBy(
      copyList,
      (a, b) => a.accountId == b.accountId
    );

    return list;
  }

  get ledgerTransactions () : Ledger []
  {
    const ledgerList: Ledger [] = [];
    const filteredList: IList []  = [...this.filterAccounts];

      filteredList.map(a => {

        const list: IList []  = this.lists.filter(e => e.accountId == a.accountId);

        const iList : ITransaction [] = [];

        list.forEach((l,i) => {

          let debit = 0;
          let credit = 0;
          let lastBalance = 0;

          if(l.type == 'Debit')
          {
            debit = l.amount;
          }
          else
          {
            credit = l.amount;
          }

          if(iList.length > 0)
          {
            const t = iList[iList.length-1];
            lastBalance =  t.balance;
          }
          else
          {
            lastBalance = this.previousBalance(l.accountId);
          }
          

          const b = lastBalance + (debit-credit);

            const p: ITransaction = {
              date: l.createdAt,
              narration: l.narration,
              debit : debit,
              credit: credit,
              balance : b,
              source : l.generatedSource
            };

            iList.push(p);
        });

        const d = {
          accountTitle : a.accountName,
          accountCode  : a.accountCode,
          accountID    : a.accountId,
          transactions : iList
        }

        ledgerList.push(d);

      });
    
    return ledgerList;
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

  previousDebit(accountID)
  {
    let total = 0;
    
    const t: PreviousTransaction [] = this.previousList.filter(e => e.accountId == accountID);
    
    if(t.length > 0)
    {
      total = t[0].totalDebit;
    }

    return  total;
  }
  
  previousCredit(accountID)
  {
    let total = 0;
    
    const t: PreviousTransaction [] = this.previousList.filter(e => e.accountId == accountID);
    
    if(t.length > 0)
    {
      total = t[0].totalCredit;
    }

    return  total;
  }
  
  previousBalance(accountID)
  {
    let total = 0;
    
    const t: PreviousTransaction [] = this.previousList.filter(e => e.accountId == accountID);
    
    if(t.length > 0)
    {
      total = t[0].totalDebit -  t[0].totalCredit;
    }

    return  total;
  }

  get currency() {
    return this.store.getters.getCurrency;
  }

  clearForFilters() {
    this.searchFilters.date1 = "";
    this.searchFilters.date2 = "";
  }

  clearSearch() {
    this.searchFilters.filterType = "None";
  }
}
</script>