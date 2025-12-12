<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-2">
            <Dropdown
              placeholder="Choose Store"
              v-model="selectedStore"
              :options="storeList"
              optionLabel="name"
              @change="loadList(0)"
            />
          </div>
          <div class="p-mx-2">
            <InputText v-model.trim="keyword" placeholder="Product Name, Generic Name, Batch No" style="width:20rem"  />
            <Button
              icon="pi pi-search "
              class="p-button-success p-mr-1"
              @click="loadSearchData"
            />
          </div>
        </template>
      </Toolbar>
      <div class="p-mt-2">
        <DataTable
          v-model:first.sync="goToFirstLink"
          :value="lists"
          dataKey="id"
          ref="dt"
          :lazy="true"
          :paginator="checkPagination"
          :rows="limit"
          :totalRecords="totalRecords"
          :resizableColumns="true"
          columnResizeMode="expand"
          responsiveLayout="scroll"
          @page="onPage($event)"
          class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
        >
          <template #empty>
            <div class="p-text-center p-p-3">No records found</div>
          </template>
          <Column :exportable="false" header="Action">
            <template #body="slotProps">
              <Button
                icon="pi pi-pencil"
                label="Adjust Stock"
                class="p-button-rounded p-button-success p-mr-2"
                @click="editIem(slotProps.data)"
              />
            </template>
          </Column>
          <!-- <Column header="Created Date" >
            <template #body="slotProps">
                {{formatDateTime(slotProps.data.created_at)}}
            </template>
          </Column>
          <Column header="Updated Date" >
            <template #body="slotProps">
              {{formatDateTime(slotProps.data.updated_at)}}
            </template>
          </Column> -->
          <Column header="Product Name" >
           <template #body="slotProps">
              {{slotProps.data.product_name}}
            </template>
          </Column>
          <Column header="Generic Name" >
           <template #body="slotProps">
                {{slotProps.data.generic}}
            </template>
          </Column>
          <Column header="Strip Size" >
           <template #body="slotProps">
              {{slotProps.data.strip_size}}
            </template>
          </Column>
          <Column header="No of Strips" >
           <template #body="slotProps">
               {{slotProps.data.pack_size}}
            </template>
          </Column>
          <Column header="Batch No" >
           <template #body="slotProps">
             {{slotProps.data.batch_no}}
            </template>
          </Column>
          <Column header="Unit Qty" >
           <template #body="slotProps">
              {{slotProps.data.qty}}
            </template>
          </Column>
          <Column :header="'Pack Purchase ('+currency+')'" >
           <template #body="slotProps">
              {{slotProps.data.purchase_price}}
            </template>
          </Column>
          <!-- <Column :header="'Pack Selling ('+currency+')'" >
           <template #body="slotProps">
               {{slotProps.data.sale_price}}
            </template>
          </Column> -->
          <Column :header="'MRP (Tax Inclusive) ('+currency+')'" >
           <template #body="slotProps">
               {{slotProps.data.mrp}}
            </template>
          </Column>
          <!-- <Column :header="'Worth Amt ('+currency+')'" >
           <template #body="slotProps">
              {{fixDigits(calculateItemWorth(slotProps.data.purchase_price,slotProps.data.qty,slotProps.data.pack_size))}}
            </template>
          </Column> -->
          <Column header="Disc %" >
           <template #body="slotProps">
             {{slotProps.data.discount_percentage}} %
            </template>
          </Column>
            <Column
                :header="taxNames[0].taxName + '(%)'"
                v-if="taxNames[0].show == 'true'"
              >
              <template #body="slotProps">
                  {{slotProps.data.tax_1}} %
                </template>
            </Column>
            <Column
              :header="taxNames[1].taxName + '(%)'"
              v-if="taxNames[1].show == 'true'"
            >
              <template #body="slotProps">
                  {{slotProps.data.tax_2}} %
              </template>
            </Column>
            <Column
              :header="taxNames[2].taxName + '(%)'"
              v-if="taxNames[2].show == 'true'"
            >
           <template #body="slotProps">
              {{slotProps.data.tax_3}} %
            </template>
          </Column>
          <Column header="Expiry Date" >
            <template #body="slotProps">
                {{formatExpiry(slotProps.data.expiry_date)}}
            </template>
          </Column>
          <Column header="Min Stock Require" >
            <template #body="slotProps">
                {{slotProps.data.min_stock}}
            </template>
          </Column>
          <Column header="Store Location" >
            <template #body="slotProps">
                {{slotProps.data.item_location}}
            </template>
          </Column>
          <Column header="Supplier Name" >
            <template #body="slotProps">
                {{slotProps.data.supplier_name || 'N/A'}}
            </template>
          </Column>
          
        </DataTable>
      </div>
    </div>
  </section>
   <Dialog
        v-model:visible="productDialog"
        :style="{ width: '100vw' }"
        :maximizable="false"
        position="top"
         class="p-fluid p-m-0 p-dialog-maximized"
      >
        <template #header>
          <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
            {{ dialogTitle }}
          </h4>
        </template>
        <p class="warning-content p-mb-3" >
          <i class="pi pi-exclamation-triangle"></i>
          WARNING: By Changing any numeric value listed in this screen will cause serious effects on accounting records of inventory.
        </p>
        <div class="p-grid">
           <div class="p-col">
            <div class="p-field">
              <label
                for="productName"
                :class="{ 'p-error': v$.productName.$invalid && submitted }"
                >Product Name</label
              >
              <InputText
                id="productName"
                v-model="v$.productName.$model"
                :class="{ 'p-invalid': v$.productName.$invalid && submitted }"
                placeholder="e.g Panadol"
              />
              <small
                v-if="
                  (v$.productName.$invalid && submitted) ||
                  v$.productName.$pending.$response
                "
                class="p-error"
                >{{
                  v$.productName.required.$message.replace(
                    "Value",
                    "Product Name"
                  )
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="generic"
                >Generic Name</label
              >
              <!-- <InputText
                id="generic"
                v-model="v$.generic.$model"
                :class="{ 'p-invalid': v$.generic.$invalid && submitted }"
                placeholder="e.g Paracetamol"
              /> -->
              <InputText
                id="generic"
                v-model="item.generic"
                placeholder="e.g Paracetamol"
              />
              <!-- <small
                v-if="
                  (v$.generic.$invalid && submitted) ||
                  v$.generic.$pending.$response
                "
                class="p-error"
                >{{
                  v$.generic.required.$message.replace("Value", "Generic Name")
                }}</small
              > -->
            </div>
            <div class="p-field">
              <label for="barcode">Barcode</label>
              <InputText id="barcode" v-model="item.barcode" />
            </div>
            <div class="p-field">
              <label for="productType">Product Type</label>
              <Dropdown
                id="productType"
                v-model="v$.productType.$model"
                :options="productType"
                placeholder="Product Type"
                :filter="true"
                optionLabel="option_name"
                optionValue="id"
              />
              <small
                v-if="
                  (v$.productType.$invalid && submitted) ||
                  v$.productType.$pending.$response
                "
                class="p-error"
                >{{
                  v$.productType.required.$message.replace(
                    "Value",
                    "Product Type"
                  )
                }}</small
              >
            </div>
            <!-- <div class="p-field">
              <label for="Description">Description</label>
              <InputText id="Description" v-model="item.description" />
            </div> -->
            <!-- <div class="p-field">
              <label for="brand">Brand</label>
              <Dropdown
                id="brand"
                v-model="v$.brand.$model"
                :options="brand"
                placeholder="Brand"
                :filter="true"
                optionLabel="option_name"
                optionValue="id"
              />
              <small
                v-if="
                  (v$.brand.$invalid && submitted) ||
                  v$.brand.$pending.$response
                "
                class="p-error"
                >{{
                  v$.brand.required.$message.replace("Value", "Brand")
                }}</small
              >
            </div> -->
          </div>
           <div class="p-col">
            <!-- <div class="p-field">
              <label for="brandSector">Brand Sector</label>
              <Dropdown
                id="brandSector"
                v-model="v$.brandSector.$model"
                :options="brandSector"
                placeholder="Brand Sector"
                :filter="true"
                optionLabel="option_name"
                optionValue="id"
              />
              <small
                v-if="
                  (v$.brandSector.$invalid && submitted) ||
                  v$.brandSector.$pending.$response
                "
                class="p-error"
                >{{
                  v$.brandSector.required.$message.replace(
                    "Value",
                    "Brand Sector"
                  )
                }}</small
              >
            </div> -->
            <div class="p-field">
              <label for="category">Category</label>
              <Dropdown
                id="category"
                v-model="v$.category.$model"
                :options="category"
                placeholder="Category"
                :filter="true"
                optionLabel="option_name"
                optionValue="id"
              />
              <small
                v-if="
                  (v$.category.$invalid && submitted) ||
                  v$.category.$pending.$response
                "
                class="p-error"
                >{{
                  v$.category.required.$message.replace("Value", "Category")
                }}</small
              >
            </div>
            <!-- <div class="p-field">
              <label for="sideEffects">Side Effects</label>
              <InputText id="sideEffects" v-model="item.sideEffects" />
            </div> -->
            <div class="p-field">
              <label
                for="packSize"
                :class="{ 'p-error': v$.packSize.$invalid && submitted }"
                >No of Strips</label
              >
              <InputNumber
                id="packSize"
                v-model="v$.packSize.$model"
                :useGrouping="false"
                :class="{ 'p-invalid': v$.packSize.$invalid && submitted }"
                @blur="setQuantityValue()"

              />
              <small
                v-if="
                  (v$.packSize.$invalid && submitted) ||
                  v$.packSize.$pending.$response
                "
                class="p-error"
                >{{
                  v$.packSize.required.$message.replace("Value", "Pack Size")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="stripSize"
                :class="{ 'p-error': v$.stripSize.$invalid && submitted }"
                >Strip Size</label
              >
              <InputNumber
                id="stripSize"
                v-model="v$.stripSize.$model"
                :class="{ 'p-invalid': v$.stripSize.$invalid && submitted }"
                :useGrouping="false"
                @blur="setQuantityValue()"

              />
              <small
                v-if="
                  (v$.stripSize.$invalid && submitted) ||
                  v$.stripSize.$pending.$response
                "
                class="p-error"
                >{{
                  v$.stripSize.required.$message.replace("Value", "Strip Size")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="pata"
                >Loose</label
              >
              <InputNumber
                id="pata"
                v-model="v$.pata.$model"
                @blur="setQuantityValue()"
                :class="{ 'p-invalid': v$.pata.$invalid && submitted }"
                :useGrouping="false"
                :value="v$.unitQty.$model - (v$.stripSize.$model*v$.packSize.$model)"
              />

            
              <small
                v-if="
                  (v$.pata.$invalid && submitted) ||
                  v$.pata.$pending.$response
                "
                class="p-error"
                >{{
                  v$.pata.required.$message.replace("Value", "Loose")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="batchNo"
                :class="{ 'p-error': v$.batchNo.$invalid && submitted }"
                >Batch No</label
              >
              <InputText
                id="batchNo"
                v-model="v$.batchNo.$model"
                :class="{ 'p-invalid': v$.batchNo.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.batchNo.$invalid && submitted) ||
                  v$.batchNo.$pending.$response
                "
                class="p-error"
                >{{
                  v$.batchNo.required.$message.replace("Value", "Batch No")
                }}</small
              >
            </div>
          </div>
          <div class="p-col">
           
             <div class="p-field">
              <label
                for="unitQty"
                :class="{ 'p-error': v$.unitQty.$invalid && submitted }"
                >Unit Qty</label
              >
              <InputNumber
                id="batchNo"
                v-model="v$.unitQty.$model"
                :class="{ 'p-invalid': v$.unitQty.$invalid && submitted }"
                :useGrouping="false"
              />
              <small
                v-if="
                  (v$.unitQty.$invalid && submitted) ||
                  v$.unitQty.$pending.$response
                "
                class="p-error"
                >{{
                  v$.unitQty.required.$message.replace("Value", "Unit Qty")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="packPurchase"
                :class="{ 'p-error': v$.packPurchase.$invalid && submitted }"
                > Purchase Price</label
              >
              <InputNumber
                id="batchNo"
                :minFractionDigits="2" 
               :maxFractionDigits="5" 
                v-model="v$.packPurchase.$model"
                :class="{ 'p-invalid': v$.packPurchase.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.packPurchase.$invalid && submitted) ||
                  v$.packPurchase.$pending.$response
                "
                class="p-error"
                >{{
                  v$.packPurchase.required.$message.replace("Value", "Pack Purchase")
                }}</small
              >
            </div>
             <div class="p-field">
              <label
                for="mRP"
                :class="{ 'p-error': v$.mRP.$invalid && submitted }"
                >MRP (Tax Inclusive)</label
              >
              <InputNumber
               :minFractionDigits="2" 
               :maxFractionDigits="5" 
                id="mRP"
                v-model="v$.mRP.$model"
                :class="{ 'p-invalid': v$.mRP.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.mRP.$invalid && submitted) ||
                  v$.mRP.$pending.$response
                "
                class="p-error"
                >{{
                  v$.mRP.required.$message.replace("Value", "Mrp")
                }}</small
              >
            </div>
            <!-- <div class="p-field">
              <label
                for="packSelling"
                >Pack Selling</label
              >
              <InputNumber
                id="packSelling"
                v-model="item.packSelling"
                :value="getPackSellingPrice()"
                :disabled="true"
              />
            </div> -->
            <div class="p-field">
              <label
                for="disc"
                :class="{ 'p-error': v$.disc.$invalid && submitted }"
                >Discount %</label
              >
              <InputNumber
                id="disc"
                v-model="v$.disc.$model"
                :class="{ 'p-invalid': v$.disc.$invalid && submitted }"
                :useGrouping="false"
              />
              <small
                v-if="
                  (v$.disc.$invalid && submitted) ||
                  v$.disc.$pending.$response
                "
                class="p-error"
                >{{
                  v$.disc.required.$message.replace("Value", "Disc %")
                }}</small
              >
            </div>
             <!-- <div class="p-field">
              <label
                for="storeLocation"
                :class="{ 'p-error': v$.storeLocation.$invalid && submitted }"
                >Store Location</label
              >
              <InputText
                id="storeLocation"
                v-model="v$.storeLocation.$model"
                :class="{ 'p-invalid': v$.storeLocation.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.storeLocation.$invalid && submitted) ||
                  v$.storeLocation.$pending.$response
                "
                class="p-error"
                >{{
                  v$.storeLocation.required.$message.replace("Value", "Store Location")
                }}</small>
            </div>  -->
          </div>
          <div class="p-col">
            
            <div class="p-field" v-if="taxNames[0].show == 'true'">
              <label
                for="tax_1"
                >{{taxNames[0].taxName}}</label
              >
              <InputNumber
                id="tax_1"
                v-model="item.tax_1"
                :useGrouping="false"
              />
            </div>
            <div class="p-field" v-if="taxNames[1].show == 'true'">
              <label
                for="tax_2"
                >{{taxNames[1].taxName}}</label
              >
              <InputNumber
                id="tax_2"
                 v-model="item.tax_2"
                :useGrouping="false"
              />
            </div>
            <div class="p-field" v-if="taxNames[2].show == 'true'">
              <label
                for="tax_3"
                >{{taxNames[2].taxName}}</label
              >
              <InputNumber
                id="tax_3"
                v-model="item.tax_3"
                :useGrouping="false"
              />
            </div>
           <div class="p-field">
              <label
                for="expiryDate"
                :class="{ 'p-error': v$.expiryDate.$invalid && submitted }"
                >Expiry Date</label
              >
              <Calendar
                id="expiryDate"
                v-model="v$.expiryDate.$model"
                selectionMode="single"
                dateFormat="dd-mm-yy"
                class="p-p-1"
                
              />
              <small
                v-if="
                  (v$.expiryDate.$invalid && submitted) ||
                  v$.expiryDate.$pending.$response
                "
                class="p-error"
                >{{
                  v$.expiryDate.required.$message.replace("Value", "Expiry Date")
                }}</small
              >
            </div>
            <div class="p-field">
              <label
                for="minStock"
                :class="{ 'p-error': v$.minStock.$invalid && submitted }"
                >Minimum Stock Requirement</label
              >
              <InputText
                id="minStock"
                v-model="v$.minStock.$model"
                :class="{ 'p-invalid': v$.minStock.$invalid && submitted }"
              />
              <small
                v-if="
                  (v$.minStock.$invalid && submitted) ||
                  v$.minStock.$pending.$response
                "
                class="p-error"
                >{{
                  v$.minStock.required.$message.replace("Value", "Store Location")
                }}</small
              >
            </div>
           
          </div>
        </div>
        <template #footer>
          <Button
            type="submit"
            label="Save"
            icon="pi pi-check"
            class="p-button-primary"
            @click.prevent="saveItem(!v$.$invalid)"
          />
        </template>
      </Dialog>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import StockService from "../../service/StockService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import { useStore, ActionTypes } from "../../store";
import { __values } from "tslib";

@Options({
  title: 'Stocks',
  components: {},
})
export default class Stocks extends Vue {
  private store = useStore();
  private lists = [];
  private dialogTitle;
  private toast;
  private goToFirstLink = 0;
  private currentStoreID = 0;
  private stockService;
  private productDialog = false;
  private submitted = false;
  private statusDialog = false;
  private keyword = "";
  private checkPagination = true;
  private totalRecords = 0;
  private limit = 0;
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Stock Management", to: "stocks" },
  ];

  private productType;
  private brand;
  private brandSector;
  private category;

   private item = {
    id: 0,
    barcode: "",
    description: "",
    image: "default.jpg",
    sideEffects: "",
    packSelling: 0,
    tax_1: 0,
    tax_2: 0,
    tax_3: 0,
    status: "Active",
  };

  private state = reactive({
    productName: "",
    generic: "",
    productType: 0,
    brand: 0,
    brandSector: 0,
    category: 0,
    stripSize: 0,
    packSize: 0,
    batchNo: "",
    unitQty: 0,
    packPurchase: 0,
    mRP: 0,
    disc: 0,
    expiryDate: "",
    storeLocation: "",
    minStock: 0,
  });

  private validationRules = {
    productName: {
      required,
    },
    // generic: {
    //   required,
    // },
    productType: {
      required,
    },
    brand: {
      required,
    },
    brandSector: {
      required,
    },
    category: {
      required,
    },
    stripSize: {
      required,
    },
    pata: {
      required,
    },
    packSize: {
      required,
    },
    batchNo: {
      required,
    },
    unitQty: {
      required,
    },
    packPurchase: {
      required,
    },
    mRP: {
      required,
    },
    disc: {
      required,
    },
    expiryDate: {
      required,
    }, 
    minStock: {
      required,
    },
  };

  // storeLocation: {
  //     required,
  //   },

  private v$ = useVuelidate(this.validationRules, this.state);

 private taxNames = [
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
    {
      taxName: "",
      show: false,
      optionalReq: "",
      taxValue: 0,
      accountHead: "",
      accountID: 0,
    },
  ];

  private storeList = [];
  private selectedStore = {
    id: 0,
  };

  //CALLING WHEN PAGINATION BUTTON CLICKS
  onPage(event) {
    this.loadList(event.first);
  }

  //DEFAULT METHOD OF TYPE SCRIPT
  //CALLING WHENEVER COMPONENT LOADS
  created() {
    this.stockService = new StockService();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList(0);
  }

  //ADD OR UPDATE THE ITEM VIA HTTP
  saveItem(isFormValid) {
    this.submitted = true;
    if (isFormValid) {

      if (this.item.id != 0) {
        this.state.expiryDate = moment(this.state.expiryDate).format(
          "YYYY-MM-DD"
        );

        this.stockService.updateItem(this.item, this.state).then((res) => {

          this.loadList(this.goToFirstLink);
          //SHOW NOTIFICATION
          this.toast.handleResponse(res);
        });
      }

      this.productDialog = false;
      this.clearItems();
    }
  }

  setQuantityValue(){
   // alert("set quantity");
    let d = Number(document.getElementById('packSize').value)*Number(document.getElementById('stripSize').value);
    let u = Number(document.getElementById('pata').value);
     //alert(d+u);
   //this.state.unitQty=(this.state.stripSize*this.state.packSize)+Number(document.getElementById('pata').value);
   this.state.unitQty = d+u;
   
  }

  //FETCH THE DATA FROM SERVER
  loadList(page) {
    this.stockService.getStocks(this.keyword,this.selectedStore.id, page).then((data) => {
      this.lists = data.records;
      this.totalRecords = data.totalRecords;
      this.limit = data.limit;
      this.storeList = data.stores;
      this.currentStoreID = data.currentStoreID;
      this.productType = data.productType;
      this.brand = data.brand;
      this.brandSector = data.brandSector;
      this.category = data.category;
      

       // //taxNames
      this.taxNames = [];

      this.taxNames.push({
        taxName: data.storeTaxes[0].tax_name_1,
        show: data.storeTaxes[0].show_1,
        optionalReq: data.storeTaxes[0].required_optional_1,
        taxValue:
          data.storeTaxes[0].show_1 == "true"
            ? Number(data.storeTaxes[0].tax_value_1)
            : 0,
        accountHead: data.storeTaxes[0].tax_name1.chartName,
        accountID: data.storeTaxes[0].link1,
      });

      this.taxNames.push({
        taxName: data.storeTaxes[0].tax_name_2,
        show: data.storeTaxes[0].show_2,
        optionalReq: data.storeTaxes[0].required_optional_2,
        taxValue:
          data.storeTaxes[0].show_2 == "true"
            ? Number(data.storeTaxes[0].tax_value_2)
            : 0,
        accountHead: data.storeTaxes[0].tax_name2.chartName,
        accountID: data.storeTaxes[0].link2,
      });

      this.taxNames.push({
        taxName: data.storeTaxes[0].tax_name_3,
        show: data.storeTaxes[0].show_3,
        optionalReq: data.storeTaxes[0].required_optional_3,
        taxValue:
          data.storeTaxes[0].show_3 == "true"
            ? Number(data.storeTaxes[0].tax_value_3)
            : 0,
        accountHead: data.storeTaxes[0].tax_name3.chartName,
        accountID: data.storeTaxes[0].link3,
      });
    });
  }

  clearItems() {
    this.item  = {
      id: 0,
      barcode: "",
      description: "",
      image: "default.jpg",
      sideEffects: "",
      packSelling: 0,
      tax_1:0,
      tax_2:0,
      tax_3:0,
      status: "Active",
  };

  this.state.stripSize = 0;
  this.state.packSize = 0;
  this.state.batchNo = "";
  this.state.unitQty = 0;
  this.state.packPurchase = 0;
  this.state.mRP = 0;
  this.state.disc = 0;
  this.state.minStock = 0;
  this.state.expiryDate = "";
  this.state.storeLocation = "";
  }

  fixDigits(amt) {
    return Number(amt).toFixed(2);
  }

  formatExpiry(date) {
    return moment(date).format("MM-YYYY");
  }

  formatDateTime(date) {
    return moment(date).format("DD-MM-YYYY hh:mm A");
  }

  calculateItemWorth(purchasePrice,qty,packSize)
  {
    let amount = 0;

    if(packSize > 0 )
    {
      amount = (qty/packSize)*purchasePrice;
    }

    return amount;
  }


  loadSearchData() {
    this.submitted = true;
    if (this.keyword) {
      this.goToFirstLink = 0;
      this.loadList(0);
    }
  }

   editIem(data) {
    this.submitted = false;
    this.dialogTitle = "Update Stock Item";
    this.productDialog = true;

    this.stockService.getItem(data).then((res) => {
      if (res != null) {
        this.item.id             = res.id;
        this.item.packSelling    = Number(res.sale_price);
        this.item.status         = res.status;
        this.item.tax_1          = Number(res.tax_1);
        this.item.tax_2          = Number(res.tax_2);
        this.item.tax_3          = Number(res.tax_3);

        this.item.barcode         = res.barcode == null ? "" : res.barcode;
        this.item.description     = res.description == null ? "" : res.description;
        this.item.image           = res.image;
        this.item.sideEffects     = res.side_effects == null ? "" : res.side_effects;

        this.state.productName    = res.product_name;
        this.state.generic        = res.generic;
        this.state.packSize       = Number(res.pack_size);
        this.state.stripSize      = Number(res.strip_size);

        this.state.productType    = res.type;
        this.state.brand          = res.brand;
        this.state.brandSector    = res.brand_sector;
        this.state.category       = res.category;
        this.state.batchNo        = res.batch_no;
        this.state.unitQty        = Number(res.qty);

        
        this.state.packPurchase   = Number(res.purchase_price);
        this.state.mRP            = Number(res.mrp);

        this.state.disc           = Number(res.discount_percentage);
        this.state.minStock       = Number(res.min_stock);
        this.state.expiryDate     = res.expiry_date;
        this.state.storeLocation  = res.item_location;
      }
    });
  }

  getPackSellingPrice(data)
  {

    const tax_1 = Number(this.item.tax_1);
    const tax_2 = Number(this.item.tax_2);
    const tax_3 = Number(this.item.tax_3);
    const mrp   = Number(this.state.mRP);

    const totalTax = tax_1 + tax_2 + tax_3;
    const avgTax = 100 + totalTax;
		const tax = (mrp / avgTax) * totalTax;
		const packPrice = (mrp - tax).toFixed(2);

    this.item.packSelling = Number(packPrice);

    return Number(packPrice);
  }

   get currency() {
        return this.store.getters.getCurrency;
    }
}
</script>

<style scoped>
.warning-content
{
  color: #c00;
}
</style>