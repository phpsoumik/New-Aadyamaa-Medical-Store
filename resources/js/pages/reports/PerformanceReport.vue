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
              class="p-button-primary px-4"
              @click="openDialog"
            />
          </div>
          <div class="">
            <Button
                icon="pi pi-file-excel"
                class="p-button-success px-4"
                @click="dt.exportCSV()"
            />
          </div>
        </template>
      </Toolbar>
      <div class="m-2 mt-4 mb-4 p-text-center">
          <h5>{{searchFilters.dimension}} {{searchFilters.reportType.name}} Performance Report</h5>
          <p>{{resultTitle}}</p>
      </div>
      <div class="p-mt-2">
       <DataTable
         ref="dt"
          :value="lists"
          :lazy="true"
          :scrollable="true"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <Column v-for="col of tableHeaders.filter(c => c.field != 'totalAmount')" :field="col.field" :header="col.header" :key="col.field" style="width:18rem;"></Column>
          <Column v-for="col of tableHeaders.filter(c => c.field == 'totalAmount')" header="Total Amount" :key="col.field" style="width:5rem;" field="totalAmount">
            <template #body="slotProps">
              {{currency}} {{ formatAmount(slotProps.data.totalAmount)}}
            </template>
          </Column>
        </DataTable>
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
              <input type="date" id="from"  v-model="searchFilters.date1" class="form-control">
            </div>
          </div>
          <div class="p-col">
            <div class="p-field">
              <label for="to">Date To</label>
              <input type="date" id="to"  v-model="searchFilters.date2" class="form-control">
            </div>
          </div>
        </div>
         <div class="p-grid p-mt-4">
            <div class="p-col">
                <label for="dimension">Dimension</label>
                <Dropdown
                  v-model="searchFilters.dimension"
                  :options="dimensions"
                  optionLabel="name"
                  optionValue="name"
                />
            </div>
            <div class="p-col">
              <label for="condition">Filter Condition</label>
              <Dropdown
                v-model="searchFilters.condition"
                :options="conditions"
                optionLabel="name"
                optionValue="value"
              />
            </div>
            <div class="p-col">
              <label for="amountValue">Amount Or Value</label>
              <InputNumber id="amountValue" mode="decimal" :maxFractionDigits="2" :minFractionDigits="2"  v-model="searchFilters.amountValue" />
            </div>
          </div>
          <div class="p-grid p-mb-4">
            <div class="p-col">
                <label for="condition">Sort By Amount or Value</label>
                <Dropdown
                  v-model="searchFilters.sort"
                  :options="sortList"
                  optionLabel="name"
                  optionValue="value"
                />
            </div>
            <div class="p-col">
              <label for="limit">Limit</label>
              <InputNumber id="limit"  v-model="searchFilters.limit" />
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
        <div class="p-grid p-mb-4">
          <div class="p-col">
                <div class="p-field">
                  <label for="reportType">Filter Type</label>
                  <Dropdown
                    v-model="searchFilters.reportType"
                    :options="reportTypes"
                    :filter="true"
                    optionLabel="name"
                    optionValue="value"
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
 import { ref } from "vue";

@Options({
  title: 'Performance Report',
  components: {},
})

export default class PerformanceReport extends mixins(UtilityOptions) {
  private dt = ref();
  private lists  = [];
  private reportService;
  private resultTitle = "";
  private productDialog = false;
  private loading = false;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Reports", to: "reports" },
    { label: "Performance Report", to: "performance-report" },
  ];

  private searchFilters = {
    id: "",
    date1: "",
    date2: "",
    reportType: {name:'Sales',value: 'INE'},
    filterType: "None",
    storeID: 0,
    condition: '>',
    dimension: 'Customer',
    sort: 'DESC',
    limit: 10,
    amountValue: 0,
  };


  private dimensions = [
    {
      name: 'Customer',
    },
    {
      name: 'User',
    },
    {
      name: 'Brand',
    },
    {
      name: 'Brand Sector',
    },
    {
      name: 'Product Type',
    },
    {
      name: 'Category',
    },
    {
      name: 'Stores',
    },
  ];

   private reportTypes = [
    {
      name: 'Sales',
      value: 'INE'
    },
    {
      name: 'Refund',
      value: 'RFD'
    },
    {
      name: 'Purchase',
      value: 'PUR'
    },
    {
      name: 'Purchase Return',
      value: 'RPU'
    },
    {
      name: 'Transfer',
      value: 'TRN'
    }
  ];

  private sortList = [
    {
      name: 'Sort by Ascending Order',
      value: 'ASC'
    },
    {
      name: 'Sort by Descending Order',
      value: 'DESC'
    }
  ];

  private conditions = [
    {
      name: '>',
      value:">"
    },
    {
      name: '<',
       value:"<"
    },
    {
      name: '<=',
      value:"<="
    },
    {
      name: '>=',
      value:">="
    },
    {
      name: '=',
      value:"="
    }
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
    this.reportService.performanceReport(this.searchFilters).then((res) => {
        const data = this.camelizeKeys(res);
        this.resultTitle = data.resultTitle;
        this.lists = data.record;
        this.loading = false;
      });
    this.productDialog = false;
  }

  get storeHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'code', header: 'Store Code'},
      {field: 'name', header: 'Store Name'},
      {field: 'contact', header: 'Contact No'},
      {field: 'email', header: 'Email Address'},
      {field: 'totalAmount', header: 'Total'},
    ];
  }

  get customerHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'customerName', header: 'Customer Name'},
      {field: 'emailAddress', header: 'Customer Email'},
      {field: 'customerContact', header: 'Customer Contact'},
      {field: 'totalAmount', header: 'Total Amount'},
    ];
  }
  
  get userHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'name', header: 'User Name'},
      {field: 'email', header: 'User Email'},
      {field: 'contact', header: 'User Contact'},
      {field: 'totalAmount', header: 'Total Amount'},
    ];
  }

  get brandHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'name', header: 'Brand Name'},
      {field: 'totalQty', header: 'Total Qty'},
      {field: 'totalAmount', header: 'Subtotal'},
    ];
  }

  get sectorHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'name', header: 'Sector Name'},
      {field: 'totalQty', header: 'Total Qty'},
      {field: 'totalAmount', header: 'Subtotal'},
    ];
  }

  get productHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'name', header: 'Product Type'},
      {field: 'totalQty', header: 'Total Qty'},
      {field: 'totalAmount', header: 'Subtotal'},
    ];
  }

  get categoryHeaders()
  {
    return  [
      {field: 'ctr', header: 'SNO'},
      {field: 'name', header: 'Category Name'},
      {field: 'totalQty', header: 'Total Qty'},
      {field: 'totalAmount', header: 'Subtotal'},
    ];
  }
  

  get tableHeaders()
  {
    let headers:any = [];

    if(this.searchFilters.dimension == 'Stores')
    {
      headers = this.storeHeaders;
    }
    else if(this.searchFilters.dimension == 'Customer')
    {
      headers = this.customerHeaders;
    }
    else if(this.searchFilters.dimension == 'User')
    {
      headers = this.userHeaders;
    }
    else if(this.searchFilters.dimension == 'Category')
    {
      headers = this.categoryHeaders;
    }
    else if(this.searchFilters.dimension == 'Brand Sector')
    {
      headers = this.sectorHeaders;
    }
    else if(this.searchFilters.dimension == 'Brand')
    {
      headers = this.brandHeaders;
    }
    else if(this.searchFilters.dimension == 'Product Type')
    {
      headers = this.productHeaders;
    }

    this.lists = [];

    return headers;
  }

   get currency() {
    return this.store.getters.getCurrency;
  }

}
</script>