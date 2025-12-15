<template>
  <section>
    <div class="app-container">
      <Toolbar>
        <template #start>
          <Breadcrumb :home="home" :model="items" class="p-menuitem-text" />
        </template>

        <template #end>
          <div class="p-mx-1">
            <Button
              icon="pi pi-times"
              label="Clear All"
              class="p-button-danger"
              @click="clearAll"
           />
          </div>
          <div class="p-mx-1">
            <Button
              icon="pi pi-plus"
              label="Add Row"
              class="p-button-success"
              @click="addNewRow"
           />
          </div>
          <div class="p-mx-1">
            <Button
              icon="pi pi-download"
              label="Download Stock CSV"
              class="p-button-warning"
              @click="downloadSample"
           />
          </div>
          <div class="p-mx-1">
             <Button
              icon="pi pi-upload"
              label="Upload Stock CSV"
              class="p-button-primary"
              @click="openFileUploader()"
            />
          </div>
          <div class="p-mx-1">
             <Button
              icon="pi pi-check"
              label="Save Stock"
              class="p-button-success"
              @click="saveNewItem(item)"
            />
          </div>
        </template>
      </Toolbar>
     <div class="p-grid p-text-center p-m-0 style-grid">
       <div class="p-col">
          <b class="p-mx-2">  TOTAL ITEMS  : {{totalItems}} </b>
          <b class="p-mx-2">  VALID ITEMS  : {{totalValid}} </b>
          <b class="p-mx-2">  INVALID ITEMS  : {{totalInValid}} </b>
          <b class="p-mx-2">  STOCK WORTH AMT : {{stockWorth}} </b>
        </div>
     </div>
      <DataTable
        :value="excelFileContent"
        responsiveLayout="scroll"
        class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
      >
        <template #empty>
          <div class="p-text-center p-p-3">No records found</div>
        </template>
            <Column >
              <template  #body="slotProps">
                <i :class="this.validateStock.includes(excelFileContent.indexOf(slotProps.data)) ? 'row-invalid' : 'row-valid'"
                class="pi pi-check-circle p-p-2"></i>
              </template>
            </Column>
            <Column header="Product Name">
              <template #body="slotProps">
                <AutoComplete
                  v-model="item.productName"
                  :suggestions="requestedMedicines"
                  @complete="searchRequestedMedicines"
                  field="display"
                  placeholder="Type medicine name (requested medicines will show first)"
                  class="p-p-1"
                  @item-select="onMedicineSelect"
                />
              </template>
            </Column>
            <Column header="Category">
              <template #body="slotProps">

              <Dropdown
                id="category"
                v-model="item.category"
                :options="category"
                placeholder="Category"
                :filter="true"
                optionLabel="option_name"
                optionValue="id"
              />
              </template>
            </Column>
             <Column header="Bar Code">
              <template #body="slotProps">
                <InputText
                  v-model="item.barcode"
                  class="p-p-1"
                />
              </template>
            </Column> 
            
            
            <Column header="Strip Size">
              <template #body="slotProps">
                <InputNumber
                  :useGrouping="false"
                  v-model="item.stripSize"
                  class="p-p-1"
                />
              </template>
            </Column>
            <Column header="No of Strips">
              <template #body="slotProps">
                <InputNumber
                  :useGrouping="false"
                  v-model="item.packSize"
                  class="p-p-1"
                  
                />
              </template>
            </Column>
            <Column header="Loose">
              <template #body="slotProps">
                <InputNumber
                  :useGrouping="false"
                  v-model="item.pata"
                  class="p-p-1"
                  
                />
              </template>
            </Column>
            <!-- <Column header="Total Quantity">
              <template #body="slotProps">
                <InputNumber
                  :useGrouping="false"
                  v-model="slotProps.data.quantity"
                  :value="getTotalQty(slotProps.data)"
                  class="p-p-1"
                  
                />
              </template>
            </Column> -->
            <Column header="Expiry Date (DD-MM-YYYY)">
              <template #body="slotProps">
                  <Calendar
                    v-model="item.expiryDate"
                    selectionMode="single"
                    dateFormat="dd-mm-yy"
                    class="p-p-1"
                    
                  />
              </template>
            </Column>
            <Column header=" Purchase Price/Strip">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="item.packPurchasePrice"
                  class="p-p-1"
                  
                />
              </template>
            </Column>
           
            <Column header="MRP(Incl. Tax)">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="item.mRP"
                  class="p-p-1"
                  
                />
              </template>
            </Column>
            <!-- <Column header="Pack Price (MRP - TAXES)">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.packSellingPrice"
                  :value="getPackSellingPrice(slotProps.data)"
                  class="p-p-1"
                  :disabled="true"
                />
              </template>
            </Column> -->
             <Column header="Batch No">
              <template #body="slotProps">
                <InputText
                  v-model="item.batchNo"
                  class="p-p-1"
                  
                />
              </template>
            </Column>
            <!--
            <Column :header="taxName1+' %'" v-if="taxName1 != ''">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.tax_1"
                  class="p-p-1"
                />
              </template>
            </Column>
            <Column :header="taxName2+' %'" v-if="taxName2 != ''">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.tax_2"
                  class="p-p-1"
                />
              </template>
            </Column>
            <Column :header="taxName3+' %'" v-if="taxName3 != ''">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.tax_3"
                  class="p-p-1"
                />
              </template>
            </Column>
            <Column header="Discount Percentage">
              <template #body="slotProps">
                <InputNumber
                  mode="decimal"
                  :maxFractionDigits="2"
                  :minFractionDigits="2"
                  v-model="slotProps.data.discountPercentage"
                  class="p-p-1"
                />
              </template>
            </Column> -->
            <!-- <Column header="Description">
              <template #body="slotProps">
                <InputText
                  v-model="slotProps.data.description"
                  class="p-p-1"
                />
              </template>
            </Column> -->
            <!-- <Column header="Minimum Stock">
              <template #body="slotProps">
                <InputNumber
                  :useGrouping="false"
                  v-model="slotProps.data.minimumStock"
                  class="p-p-1"
                />
              </template>
            </Column> -->
            <!-- <Column header="Store Locations">
              <template #body="slotProps">
                <InputText
                  v-model="slotProps.data.storeLocations"
                  class="p-p-1"
                />
              </template>
            </Column> -->
          
            <Column :exportable="false" header="Action">
              <template #body="slotProps">
                <span class="p-buttonset">
                  <Button
                    icon="pi pi-trash"
                    class="p-button-rounded p-button-danger"
                  @click="clearListItem(item)"
                  />
                </span>
              </template>
            </Column>
      </DataTable>
      <FileUploader
        :uploaderDetail="{
          status: uploaderStatus,
          dialogTitle: 'Upload Stock CSV file:',
          imageType: '.csv',
        }"
        v-on:updateUploaderStatus="updateUploaderStatus"
      />

    </div>
  </section>
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import StockService from "../../service/StockService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, minLength, maxLength } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import AutoComplete from "primevue/autocomplete";

import FileUploader from "../../components/FileUploader.vue";


@Options({
  title: 'Import Stock',
  components: {
    AutoComplete,
    FileUploader
  },
})
export default class ImportStock extends Vue {

  private taxName1 = '';
  private taxName2 = '';
  private taxName3 = '';

  private totalInValid = 0;
  private totalValid = 0;


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

  private toast;
  private stockService;
  private uploaderStatus = false;
  private requestedMedicines = [];
  private home = { icon: "pi pi-home", to: "/" };
  private items = [
    { label: "Initialization", to: "initialization" },
    { label: "Import Stock", to: "import-stock" },
  ];

  private item = 
    {
      'product_name' : '',
			'genericName' : '',
			'barcode' : '',
			'productType' : 0,
			'brandName' : 0,
			'brandSector' : 0,
			'category' : 0,
			'sideEffects' : '',
			'stripSize' : 0,
			'packSize' : 0,
			'quantity' : 0,
			'expiryDate' : '',
			'packPurchasePrice' : 0,
			'packSellingPrice' : 0,
			'mRP' : 0,
			'batchNo' : '',
			'tax_1' : 0,
			'tax_2': 0 ,
			'tax_3' : 0,
			'discountPercentage' : 0,
			'description' : '',
			'minimumStock' : 0,
			'storeLocations' : '',
      'invoiceNo': 100,
      'total': 200,
      'discount': 50,
      'pata':0
    }
  

  private excelFileContent = [
    {
      'productName' : '',
			'genericName' : '',
			'barcode' : '',
			'productType' : 0,
			'brandName' : 0,
			'brandSector' : 0,
			'category' : 0,
			'sideEffects' : '',
			'stripSize' : 0,
			'packSize' : 0,
			'quantity' : 0,
			'expiryDate' : '',
			'packPurchasePrice' : 0,
			'packSellingPrice' : 0,
			'mRP' : 0,
			'batchNo' : '',
			'tax_1' : 0,
			'tax_2': 0 ,
			'tax_3' : 0,
			'discountPercentage' : 0,
			'description' : '',
			'minimumStock' : 0,
			'storeLocations' : '',
      'invoiceNo': 100,
      'total': 200,
      'discount': 50
    }
  ];


  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.stockService = new StockService();
    this.toast = new Toaster();
  }

  //CALLNING AFTER CONSTRUCTOR GET CALLED
  mounted() {
    this.loadList();
    

  }

  //OPEN DIALOG TO ADD NEW ITEM
  downloadSample() {
    this.stockService.exportSampleStock().then((res) => {
      let fileURL = window.URL.createObjectURL(new Blob([res]));
      let fileLink = document.createElement('a');
      fileLink.href = fileURL;
      fileLink.setAttribute('download','sampleData.csv');
      document.body.appendChild(fileLink);
      fileLink.click();
    });
  }

  //FETCH THE DATA FROM SERVER
  loadList() {
    this.stockService.getItems().then((data) => {
      this.productType  = data.productType;
      this.brand        = data.brand;
      this.brandSector  = data.brandSector;
      this.category     = data.category;

      this.taxName1 = (data.storeTaxes[0].show_1 == 'true' ? data.storeTaxes[0].tax_name_1 : '');
      this.taxName2 = (data.storeTaxes[0].show_2 == 'true' ? data.storeTaxes[0].tax_name_2 : '');
      this.taxName3 = (data.storeTaxes[0].show_3 == 'true' ? data.storeTaxes[0].tax_name_3 : '');
    });
  }



  public openFileUploader()
  {
    //alert(" open file uoplaoded called");
    this.uploaderStatus = true;
  }


  updateUploaderStatus(params)
  {
    this.uploaderStatus = false;
    if(params.length > 0)
    {
      this.excelFileContent = [];
      //alert("parma len >0"+params[0]);
      
      this.stockService.uploadCSVFile(params[0]).then((res) => {
       // alert(" stockservice upload csv passed");

          if(res.length > 0)
          {

            let categoryID = 1;
            let bandID = 1;
            let bandSectorID = 1;
            let productTypeID = 1;
            //alert(" res.legth >0");

            res.forEach(e => {
              if( e[0] != 'Product Name (*) [Text]')
             {
                //alert(" reading csv content batch no "+e[30]);

                // let categoryID = 0;
                // let bandID = 0;
                // let bandSectorID = 0;
                // let productTypeID = 0;

                // this.productType.forEach(i => {
                //   if(i.option_name == e[3])
                //   {
                //     productTypeID =  i.id;
                //   }
                // });

                // this.brand.forEach(i => {
                //   if(i.option_name == e[4])
                //   {
                //     bandID =  i.id;
                //   }
                // });

                // this.brandSector.forEach(i => {
                //   if(i.option_name == e[5])
                //   {
                //     bandSectorID =  i.id;
                //   }
                // });
                
                // this.category.forEach(i => {
                //   if(i.option_name == e[6])
                //   {
                //     categoryID =  i.id;
                //   }
                // });

                this.excelFileContent.push(
                  {
                    // 'productName' :         e[0],
                    // 'genericName' :         e[1],
                    // 'barcode' :             e[2],
                    // 'productType' :         productTypeID,
                    // 'brandName' :           bandID,
                    // 'brandSector' :         bandSectorID,
                    // 'category' :            categoryID,
                    // 'sideEffects' :         e[7],
                    // 'stripSize' :           Number(e[8]),
                    // 'packSize' :            Number(e[9]),
                    // 'quantity' :            Number(e[10]),
                    // 'expiryDate' :          e[11],
                    // 'packPurchasePrice' :   Number(e[12]),
                    // 'packSellingPrice' :    0,
                    // 'mRP' :                 Number(e[13]),
                    // 'batchNo' :             e[14],
                    // 'tax_1' :               Number(e[15]),
                    // 'tax_2':                Number(e[16]),
                    // 'tax_3' :               Number(e[17]),
                    // 'discountPercentage' :  Number(e[18]),
                    // 'description' :         e[19],
                    // 'minimumStock' :        Number(e[20]),
                    // 'storeLocations' :      e[21],

                    'productName' :         e[5],
                    'genericName' :         'not specified',
                    'barcode' :             e[30], //actually storing the hsn code
                    'productType' :         productTypeID,
                    'brandName' :           bandID,
                    'brandSector' :         bandSectorID,
                    'category' :            categoryID,
                    'sideEffects' :         'side effect',
                    'stripSize' :           Number(e[6]),
                    'packSize' :            Number(e[20]),
                    'quantity' :            Number(e[20]),
                    'expiryDate' :          '22-07-2026',
                    'packPurchasePrice' :   Number(e[13]),
                    'packSellingPrice' :    0,
                    'mRP' :                 Number(e[16]),
                    'batchNo' :             e[8],
                    'tax_1' :               Number(e[11]/2),
                    'tax_2':                Number(e[11]/2),
                    'tax_3' :               Number(0),
                    'discountPercentage' :  Number(0),
                    'description' :         'no description',
                    'minimumStock' :        Number(0),
                    'storeLocations' :      'Unknown',
                    'invoiceNo': 100,
                    'discount':150,
                    'total': 300,

                    
                  }
                );
              }
            });
          }
      });
    }
  }

  
  saveNewItem(e){
    // alert("saving new item "+JSON.stringify(e));

    this.stockService.addTempStock(e).then((res) => {
        this.clearAll();
        this.toast.handleResponse(res);
      });
    

  }

  saveFileData()
  {
    //alert("Saving");
    if (this.validateStock.length == 0) {
      this.stockService.save(this.excelFileContent).then((res) => {
        this.clearAll();
        this.toast.handleResponse(res);
      });
    }
    else
    {
      this.toast.showWarning('Some of the fields are invalid'); 
    }
  }

  //sam
  getTotalQty(data){
    const stripSize = Number(data.stripSize);
    const packSize = Number(data.packSize);

    return stripSize * packSize;
  }

  getPackSellingPrice(data)
  {
    const tax_1 = Number(data.tax_1);
    const tax_2 = Number(data.tax_2);
    const tax_3 = Number(data.tax_3);
    const mrp   = Number(data.mRP);

    const totalTax = tax_1 + tax_2 + tax_3;
    const avgTax = 100 + totalTax;
		const tax = (mrp / avgTax) * totalTax;
		const packPrice = (mrp - tax).toFixed(2);

    data.packSellingPrice = Number(packPrice);

    return Number(packPrice);
  }


  clearAll() {
    this.excelFileContent = [];
    this.totalInValid = 0;
    this.totalValid = 0;
    this.addNewRow();
    this.toast.showSuccess("Cleared Successfully");
  }

  addNewRow()
  {
    this.excelFileContent.push({
      'productName' : '',
			'genericName' : '',
			'barcode' : '',
			'productType' : 0,
			'brandName' : 0,
			'brandSector' : 0,
			'category' : 0,
			'sideEffects' : '',
			'stripSize' : 0,
			'packSize' : 0,
			'quantity' : 0,
			'expiryDate' : '',
			'packPurchasePrice' : 0,
			'packSellingPrice' : 0,
			'mRP' : 0,
			'batchNo' : '',
			'tax_1' : 0,
			'tax_2': 0 ,
			'tax_3' : 0,
			'discountPercentage' : 0,
			'description' : '',
			'minimumStock' : 0,
			'storeLocations' : '',
      'invoiceNo' :0,
      'discount':0,
      'total':0,
    });
  }

  clearListItem(item) {
    this.excelFileContent.splice(this.excelFileContent.indexOf(item), 1);
    this.toast.showSuccess("Row Deleted Successfully");
  }

  // Search requested medicines for autocomplete
  searchRequestedMedicines(event) {
    console.log('Search triggered with query:', event.query);
    if (event.query.length >= 2) {
      this.stockService.getPendingRequestedMedicines(event.query).then((medicines) => {
        console.log('Received medicines:', medicines);
        this.requestedMedicines = medicines;
      }).catch((error) => {
        console.error('Error fetching medicines:', error);
      });
    }
  }

  // Handle medicine selection
  onMedicineSelect(event) {
    if (event.value && event.value.is_requested) {
      this.item.productName = event.value.medicine_name;
      // Show notification that this is a requested medicine
      this.toast.showInfo(`This medicine was requested by: ${event.value.customer_name} (${event.value.customer_phone})`);
    }
  }

  get stockWorth()
  {
    let totalAmount = 0;
    this.excelFileContent.forEach(e => {
        if(e.packSize != 0)
        {
          totalAmount = totalAmount + Number((e.quantity/e.packSize) * e.packPurchasePrice);
        }
    });
    return totalAmount.toFixed(2);
  }
  
  getPackWorth(data)
  {
    let totalAmount = 0;
    this.excelFileContent.forEach(e => {
      if(data.packSize != 0)
      {
        totalAmount =  Number((data.quantity/data.packSize) * data.packPurchasePrice);
      }
    });
   // return totalAmount.toFixed(2);
   //sam
   return data.packPurchasePrice*data.packSize;
  }

  get totalItems()
  {
    return  this.excelFileContent.length;
  }

  get validateStock() {
    this.totalInValid = 0;
    this.totalValid = 0;
    let invalidListItems: Number[] = [];
    this.excelFileContent.map((v, index) => {

      //  v.productName == null || v.productName == "" || v.genericName == null || v.genericName == "" ||
      //   v.expiryDate == null || v.expiryDate == "" ||
      //   v.batchNo == null || v.batchNo == "" ||
      //   v.productType == 0 || v.brandName == 0 ||  v.brandSector == 0 ||
      //   v.category == 0 || v.packSize <= 0 || v.quantity <= 0 || v.packPurchasePrice <= 0 ||
      //   v.mRP <= 0

      if (
        // v.productName == null ||
        // v.batchNo == null || v.batchNo == "" ||
        // v.productType == 0 || v.brandName == 0 ||  v.brandSector == 0 ||
        // v.category == 0 || v.packSize <= 0 || v.quantity <= 0
        v.productName == null ||
        v.batchNo == null || v.batchNo == "" ||
        v.productType == 0 ||  v.packSize <= 0 || v.quantity <= 0
        ) {
        this.totalInValid++;
        invalidListItems.push(index);
      }
      else
      {
         this.totalValid++;
      }
    });
    return invalidListItems;
  }
}
</script>

<style scoped>
.p-calendar
{
  width: 250px;
}

.style-grid
{
  font-size: 14px;
  background-color: green;
  color:#fff;
}

.row-invalid
{
  color:#fff; 
  background-color:#c00; 
  border-radius:5px;
}

.row-valid
{
  color:#fff; 
  background-color:green; 
  border-radius:5px;
}
</style>