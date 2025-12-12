<template>
  <Dialog
    v-model:visible="productDialog"
    :style="{ width: '50vw' }"
    :maximizable="true"
    position="top"
    class="p-fluid"
    :modal="true"
    :closable="true"
    @hide="closeDialog"
  >
    <template #header>
      <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
        <i class="pi pi-search"></i> {{ dialogTitle }}
      </h5>
    </template>

    <div class="p-field">
      <label for="dateFrom">Date From</label>
      <Calendar
          id="dateFrom"
          v-model="item.date1"
          selectionMode="single"
          dateFormat="dd-mm-yy"
          @click="clearFilterName"
        />
    </div>
    <div class="p-field">
      <label for="dateTo">Date To</label>
      <Calendar
          id="dateTo"
          v-model="item.date2"
          selectionMode="single"
          dateFormat="dd-mm-yy"
          @click="clearFilterName"
        />
    </div>
    <div class="p-field">
      <label for="filterName">Or Filter</label>
      <Dropdown
        id="filterName"
        v-model="item.filterName"
        :options="filterList"
        optionLabel="key"
        @change="clearDates"
      />
    </div>
    <div class="p-field">
      <label for="storeID">Store</label>
      <Dropdown
        id="storeID"
         v-model="item.storeID"
        :options="storeList"
        optionLabel="name"
      />
    </div>
    <template #footer >
      <Button
        type="submit"
        label="Search"
        icon="pi pi-search"
        class="p-button-primary"
        @click.prevent="searchRecords()"
      />
    </template>
  </Dialog>
</template>

<script lang="ts">
import moment from "moment";
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import VoucherService from "../service/VoucherService.js";

@Options({
  props: {
    searchDetail: Object,
  },
  watch: {
    searchDetail(obj) {
      
      this.openDialog();
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
    },
  },
  emits: ["updateFilterStatus"],
})
export default class SearchFilter extends Vue {
  private toast;
  private productDialog = false;
  private dialogTitle = "";
  private voucherService;

  private item = {
    id: 0,
    loading: 'No',
    filterName:  {'key' : "None",'value' : "None" },
    date1: "",
    date2: "",
    storeID: {'id' : 0},
  };


  private storeList;
  private filterList;

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.toast = new Toaster();
    this.voucherService = new VoucherService();
  }

  mounted() {
    this.getTheStoreFilterList();
  }


  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.productDialog = true;
  }


  closeDialog() {
    this.$emit("updateFilterStatus", {});
    this.productDialog = false;
  }

  searchRecords() {
    this.productDialog = false;
    this.item.loading = 'Yes';
    if(this.item.date1 != '' && this.item.date2 != '')
    {
      this.item.date1 = moment(this.item.date1).format("YYYY-MM-DD");
      this.item.date2 = moment(this.item.date2).format("YYYY-MM-DD");
    }
   
    this.$emit("updateFilterStatus", this.item); 
  }

  getTheStoreFilterList()
  {
    this.voucherService.getFilterList().then((res) => {
      this.storeList = res.stores;
      this.filterList = res.datesList;
    });
  }

  clearDates()
  {
    this.item.date1 = "";
    this.item.date2 = "";
  }

  clearFilterName()
  {
    this.item.filterName = {'key' : "None",'value' : "None" };
  }
}
</script>