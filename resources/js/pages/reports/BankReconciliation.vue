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
          <h5>{{storeName}} Statement of Bank Reconcile</h5>
          <p>{{searchFilters.bank.bank}} {{searchFilters.bank.branch}} {{searchFilters.bank.title}} ({{searchFilters.bank.number}} )</p>
          <p>{{resultTitle}}</p>
      </div>
      <div class="p-mt-2">
        <table class="table table-bordered table-hover">
           <tr>
             <td class="p-p-1" style="background-color:#eee;" colspan="6"><i class="pi pi-minus-circle"></i> Deduct (Outstanding Check) 
             <h5 style="float:right;">Balance before {{beforeStatement}} : {{formatAmount(beforeStatementAmount)}}</h5> </td>
           </tr> 
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">DATE</th>
             <th class="p-p-1">PARTICULAR</th>
             <th class="p-p-1">TYPE</th>
             <th class="p-p-1">RECEIPT NO</th>
             <th class="p-p-1">NAME</th>
             <th class="p-p-1">AMOUNT</th>
           </tr>
            <tr  v-for="i in outStandingCheque" :key="i">
              <td class="p-p-1">{{formatDate(i.receiptDate)}}</td>
              <td class="p-p-1">{{i.accountHead}}</td>
              <td class="p-p-1">{{convertToFull(i.type)}}</td>
              <td class="p-p-1">{{i.receiptNo}}</td>
              <td class="p-p-1">{{i.profileName.profileName}}</td>
              <td class="p-p-1">{{formatAmount(i.amount)}}</td>
            </tr>
            <tr style="background-color:#6c757d; color:#fff;">
              <td class="p-p-1" colspan="5"></td>
              <td class="p-p-1">TOTAL <span style="float:right">{{currency}}  {{formatAmount(totalOutStanding)}}</span></td>
            </tr>
           <tr>
             <td class="p-p-1" style="background-color:#eee;" colspan="6"><i class="pi pi-plus-circle"></i> ADD </td>
           </tr> 
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">DATE</th>
             <th class="p-p-1">PARTICULAR</th>
             <th class="p-p-1">TYPE</th>
             <th class="p-p-1">RECEIPT NO</th>
             <th class="p-p-1">NAME</th>
             <th class="p-p-1">AMOUNT</th>
           </tr>
            <tr  v-for="i in addTransactions" :key="i">
              <td class="p-p-1">{{formatDate(i.receiptDate)}}</td>
              <td class="p-p-1">{{i.accountHead}}</td>
              <td class="p-p-1">{{convertToFull(i.type)}}</td>
              <td class="p-p-1">{{i.receiptNo}}</td>
              <td class="p-p-1">{{i.profileName.profileName}}</td>
              <td class="p-p-1">{{formatAmount(i.amount)}}</td>
            </tr>
            <tr style="background-color:#6c757d; color:#fff;">
              <td class="p-p-1" colspan="5"></td>
              <td class="p-p-1">TOTAL <span style="float:right"> {{currency}} {{formatAmount(totalAddTransactions)}}</span></td>
            </tr>
           <tr>
             <td class="p-p-1" style="background-color:#eee;" colspan="6"><i class="pi pi-minus-circle"></i> DEDUCT </td>
           </tr> 
           <tr style="background-color:#004C97; color:#fff;">
             <th class="p-p-1">DATE</th>
             <th class="p-p-1">PARTICULAR</th>
             <th class="p-p-1">TYPE</th>
             <th class="p-p-1">RECEIPT NO</th>
             <th class="p-p-1">NAME</th>
             <th class="p-p-1">AMOUNT</th>
           </tr>
           <tr  v-for="i in deductTransactions" :key="i">
              <td class="p-p-1">{{formatDate(i.receiptDate)}}</td>
              <td class="p-p-1">{{i.accountHead}}</td>
              <td class="p-p-1">{{convertToFull(i.type)}}</td>
              <td class="p-p-1">{{i.receiptNo}}</td>
              <td class="p-p-1">{{i.profileName.profileName}}</td>
              <td class="p-p-1">{{formatAmount(i.amount)}}</td>
            </tr>
            <tr style="background-color:#6c757d; color:#fff;">
              <td class="p-p-1" colspan="5"></td>
              <td class="p-p-1">TOTAL <span style="float:right">{{currency}} {{formatAmount(totalDeductTransactions)}}</span></td>
            </tr>
            <tr style=" background-color:#6c757d; color:#fff;">
              <td class="p-p-1" colspan="6">
                <h5>
                  <span style="float:left;">  Opening Balance : {{currency}} {{formatAmount(openingBalance)}} ({{formatDate(openingBalanceDate)}}) </span>
                  <span style="float:right;">  Balance till {{endStatement}} : {{currency}} {{formatAmount(totalBalanceAmount)}} </span>
                </h5>
              </td>
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
            <label for="filterStore">Filter Month</label>
            <Dropdown
              v-model="searchFilters.filterType"
              :options="dateFilters"
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
              <label for="from">Choose Month</label>
              <input type="date" id="from"  v-model="searchFilters.date1" @change="clearSearch" class="form-control">
            </div>
          </div>
        </div>
        <div class="p-grid">
          <div class="p-col">
             <div class="p-field">
                <label for="filterStore">Bank List</label>
                 <Dropdown v-model="searchFilters.bank" :options="banksList" placeholder="Select a bank account">
                    <template #value="slotProps">
                        <div>{{slotProps.value.bank}} | {{slotProps.value.branch}} | {{slotProps.value.number}} | {{slotProps.value.title}}</div>
                    </template>
                    <template #option="slotProps">
                        <div>{{slotProps.option.bank}} | {{slotProps.option.branch}} | {{slotProps.option.number}} | {{slotProps.option.title}}</div>
                    </template>
                </Dropdown>
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
import AutoComplete from "primevue/autocomplete";


interface ITransaction {
  accountHead   : string;
  accountId     : number;
  amount        : string;
  bankId        : number;
  branchId      : number;
  createdAt     : string;
  createdBy     : number;
  description   : string;
  id            : number;
  receiptDate   : string;
  receiptNo     : string;
  status        : string;
  transactionId : number;
  transactionNo : string;
  type          : string;
  updatedAt     : string;
}

@Options({
  title: 'Statement of Bank Reconcile',
  components: {AutoComplete},
})

export default class BankReconciliation extends mixins(UtilityOptions) {
  private lists : ITransaction [] = [];
  private reportService;
  private resultTitle     = "";
  private beforeStatementAmount     = 0;
  private openingBalance     = 0;
  private openingBalanceDate = "";
  private beforeStatement = "";
  private endStatement = "";
  private storeName = "";
  private productDialog = false;
  private loading = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Reports", to: "reports" },
    { label: "Statement of Bank Reconcile", to: "bank-reconciliation" },
  ];

  private searchFilters = {
    id: "",
    date1: "",
    filterType: "None",
    storeType: "all_stores",
    storeID: 0,
    bank: {},
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

  private dateFilters = [
    {
      key: "None",
      value: "None" 
    },
    {
      key: "This Month",
      value: "this_month" 
    },
    {
      key: "Last Month",
      value: "last_month" 
    }
  ];

  private filterDates = [];
  private dialogTitle;
  private submitted = false;
  private filterBranch = [];
  private banksList = [];

  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.reportService = new StoreReports();
  }
  
   //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.storeList();
    this.bankList();
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
    });
  }
  
  bankList()
  {
    this.reportService.getAllBanks().then((res) => {
      this.banksList  = res.records;
      if(this.banksList != null)
      {
        this.searchFilters.bank = this.banksList[0];
        this.loadList();
      }  
    });
  }
 
  // USED TO GET SEARCHED ASSOCIATE
  loadList() {
    this.loading = true;
    this.reportService.bankStatement(this.searchFilters).then((res) => {
        const data = this.camelizeKeys(res);
        this.resultTitle = data.resultTitle;
        this.storeName = data.storeName;
        this.lists = data.record;
        this.beforeStatement = data.beforeStatement;
        this.endStatement    = data.endStatement;
        this.storeName    = data.storeName;
        this.beforeStatementAmount    = data.beforeStatementAmount;
        this.openingBalance        = data.openingBalance;
        this.openingBalanceDate    = data.openingBalanceDate;
        this.loading = false;  
      });
    this.productDialog = false;
  }

  get outStandingCheque()
  {
    return this.lists.filter(e => e.status == 'Outstanding');
  }

  get totalOutStanding()
  {
    let t = 0;
    this.outStandingCheque.forEach(e => {
      t = t + Number(e.amount);
    });
    return t;
  }
  
  get deductTransactions()
  {
    return this.lists.filter(e => (e.type == 'CHQ' && e.status == 'Active') || e.type == 'EXP' || e.type == 'FTR' || e.type == 'RFD' || e.type == 'PUR' || e.type == 'RFR');
  }

   get totalDeductTransactions()
  {
    let t = 0;
    this.deductTransactions.forEach(e => {
      t = t + Number(e.amount);
    });
    return t;
  }
  
  get addTransactions()
  {
    return this.lists.filter(e => e.type == 'DPT' || e.type == 'REF' || e.type == 'RPU' || e.type == 'INE' || e.type == 'TRN' || e.type == 'INV' || e.type == 'SLS');
  }

  get totalAddTransactions()
  {
    let t = 0;
    this.addTransactions.forEach(e => {
      t = t + Number(e.amount);
    });
    return t;
  }

  get totalBalanceAmount()
  {
    return this.openingBalance + (this.beforeStatementAmount-this.totalOutStanding-this.totalDeductTransactions+this.totalAddTransactions);
  }

  get currency() {
    return this.store.getters.getCurrency;
  }

  clearForFilters() {
    this.searchFilters.date1 = "";
  }

  clearSearch() {
    this.searchFilters.filterType = "None";
  }
}
</script>