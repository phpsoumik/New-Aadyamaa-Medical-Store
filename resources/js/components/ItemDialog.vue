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
        <i class="pi pi-check-circle"></i> {{ dialogTitle }}
      </h5>
    </template>

    <div class="p-field">
      <label
        for="accountTitle"
        >Product Name</label
      >
      
      <InputText
        id="accountTitle"
        v-model.trim="this.item.product_name"
        autoFocus
      />
      
    </div>
    <div class="p-field">
      <label
        for="contactNo"
        >Generic Name</label
      >
      <InputText
        id="contactNo"
        v-model.trim="this.item.generic" 
      />
      <!-- v-model.trim="v$.contactNo.$model" -->

      
    </div>
    <div class="p-field">
      <label for="emailAddress">Drug Type</label>
      <Dropdown
            id="type"
            v-model="this.item.brand_sector"
            :options="brandSector"
             optionLabel="option_name"
            optionValue="id"
                  
        />    </div>
    <div class="p-field">
      <label for="nationalId">Tax Type</label>
      <Dropdown
            id="type"
            v-model="this.item.type"
            :options="productType"
             optionLabel="option_name"
            optionValue="id"
                  
        />
    </div>
    <div class="p-field">
      <label for="address">Brand</label>
      <Dropdown
            id="type"
            v-model="this.item.brand"
            :options="brand"
             optionLabel="option_name"
            optionValue="id"
                  
        />
        <a href="javascript:;" @click.prevent="toggleBrand()" >Add a Brand</a>

     
    </div>

    <div class="p-field" id="brandArea" style="display: none;">
        <InputText id="newbrand"/>
        <Button
          type="submit"
          label="Save"
          icon="pi pi-check"
          class="p-button-primary"
          @click.prevent="saveBrand()"
      />

    </div>
    <div class="p-field">
      <label for="accountType">Category</label>
      <Dropdown
        id="accountType"
        v-model="item.category"
        :options="category"
        optionLabel="option_name"
        optionValue="id"      />
    </div>
    <div class="p-field">
      <label for="strip">Strip Size</label>
      <InputText id="strip" v-model.trim="item.strip_size" />
    </div>
    <div class="p-field">
      <label for="description">Min. Stock</label>
      <InputText id="description" v-model.trim="item.min_stock" />
    </div>
    <div class="p-field">
      <label for="location">Rack No</label>
      <InputText id="location" v-model.trim="item.item_location" />
    </div>
    <template #footer v-if="!previewOnly">
      <Button
        type="submit"
        label="Save"
        icon="pi pi-check"
        class="p-button-primary"
        @click.prevent="saveItem()"
      />
    </template>
  </Dialog>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import { reactive } from "vue";
import ProfilerService from "../service/ProfilerService.js";
import StockService from "../service/StockService.js";
import OptionService from "../service/OptionTag.js";

import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import router from "../router";
import OptionTags from "pages/optiontags/OptionTags.vue";


@Options({
  props: {
    itemDetails: Object,
  },
  watch: {
    itemDetails(obj) {
      if (obj.statusType == "New") {
        // alert('new product');
        this.previewOnly = false;
        this.openDialog();
      } 
      else if (obj.statusType == "Update") {
        this.previewOnly = false;
        this.item.id = obj.profilerID;
        this.editIem();
      }
      else if (obj.statusType == "Preview") {
        this.previewOnly = true;
        this.item.id = obj.profilerID;
        this.editIem();
      } 
      else {
        this.dialogTitle = "";
      }
      
      this.dialogTitle = obj.dialogTitle;
      this.productDialog = obj.status;
      this.currentUserID = obj.currentUserID;
    },
  },
  emits: ["updateProfilerStatus"],
})
export default class ItemDialog extends Vue {
  private toast;
  private submitted = false;
  private productDialog = false;
  private previewOnly = false;
  private dialogTitle = "";
  private profilerService;
  private stockService;

  private optionService;
  private currentUserID = 0;

  private item = {
    id: 0,
    product_name:'',
        generic:'',
        barcode:0,
        type:0,
        description:'',
        brand:0,
        brand_sector:0,
        category:0,
        // 'side_effects',
        // 'pack_size',
         'strip_size':0,
        // 'expiry_date',
        // 'qty',
        // 'strip_size',
        // 'pack_size',
        // 'sale_price',
        // 'purchase_price',
        // 'mrp',
        // 'batch_no',
        // 'tax_1',
        // 'tax_2',
        // 'tax_3',
        // 'discount_percentage',
        min_stock:0,
        item_location:0,
        created_by:0,
        status:'Active',
        // 'branch_id',
        // 'hsn'


  };

  private state = reactive({
    accountTitle: "",
    contactNo: "",
  });

  // private validationRules = {
  //   accountTitle: {
  //     required,
  //   },
  //   contactNo: {
  //     required,
  //   },
  // };

  //private v$ = useVuelidate(this.validationRules, this.state);

  private productType = [
    {
      id : 0,
      option_name : '',
    }
  ];
  private brand  = [
    {
      id : 0,
      option_name : '',
    }
  ];     
  private brandSector = [
    {
      id : 0,
      option_name : '',
    }
  ];
  private category   = [
    {
      id : 0,
      option_name : '',
    }
  ]; 

  private brandItem = {
    id: 0,
    optionType: { key: "Brands" },
    description: "",
    status: "Active",
  };


  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    //this.profilerService = new ProfilerService();
    this.stockService = new StockService();
    this.optionService = new OptionService();

    this.toast = new Toaster();
  }

  mounted(): void {
    this.loadList();

  }

  //OPEN DIALOG TO ADD NEW ITEM
  openDialog() {
    this.submitted = false;
    this.productDialog = true;
    this.clearItems();
  }

  clearItems() {
    // this.item = {
    //   id: 0,
    // productName:'',
    // genericName:'',
    // drugType:0,
    // productType:'',
    // brandSector:0,
    // category:0,
    // brand:0,
    // minStock:0,
    // itemLocation:0,
    // };

    this.state.accountTitle = "";
    //this.state.contactNo = "";
  }

  closeDialog() {
    this.$emit("updateProfilerStatus", ["",{}]);
    this.productDialog = false;
  }

   toggleBrand(){
    // alert('toogle');
    document.getElementById("brandArea").style.display='block';
    return false;

  }

  saveBrand(){

    // alert('save brand '+document.getElementById("newbrand").value);
    this.optionService.saveItem(this.brandItem, document.getElementById("newbrand").value);
    document.getElementById("brandArea").style.display='none';
    this.loadList();
    

  }
  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem() {
    this.submitted = true;
    let isFormValid=true;
   
    this.stockService
          .saveNewItem(this.item)
          .then((res) => {
            //alert(res);
            this.$emit("updateProfilerStatus", ["load",{}]);
            //SHOW NOTIFICATION
            this.toast.handleResponse(res);
            //this.productDialog=false;
            this.closeDialog();
            //clear the dialog screen
           // this.item={};
            
          });       
    
  }

  //FETCH THE DATA FROM SERVER
  loadList() {
    this.stockService.getItems().then((data) => {
      this.productType  = data.productType;
      this.brand        = data.brand;
      this.brandSector  = data.brandSector;
      this.category     = data.category;


    });
  }
}
</script>