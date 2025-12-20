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
                <h5>Stock Expiry Report</h5>
                <p>{{ resultTitle }}</p>
            </div>
            <div class="p-mt-2" >





                
                <DataTable
                    :value="rList"
                    :lazy="true"
                     dataKey="id" 
                    v-model:selection="selectedProducts"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                    :resizableColumns="true"
                    :loading="loading"
                    :autoLayout="true"
                    scrollHeight="70vh"
                    responsiveLayout="scroll"
                    :scrollable="true"
                    id="maincontent"
                    editMode="cell" 
                    @cell-edit-complete="onCellEditComplete"
                >
                    <template #empty>
                        <div class="p-text-center p-p-3">No records found</div>
                    </template>
                    <!-- THIS IS SOUMIK CODE - Smaller checkbox column -->
                    <Column selectionMode="multiple" headerStyle="width: 2rem"></Column>
                    <!-- THIS IS SOUMIK CODE - Wider product name column -->
                    <Column field="itemName" header="Product" style="min-width: 200px; max-width: 250px;">
                        <template #body="{ data }">
                            <span :title="data.itemName" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ data.itemName }}
                            </span>
                        </template>
                    </Column>
                    <!-- THIS IS SOUMIK CODE - Hidden Stock ID column -->
                    <!-- <Column field="stockId" header="Stock ID" :sortable="true" style="min-width: 120px;">
                        <template #body="{ data }">
                            <span class="font-weight-bold text-primary">{{ data.stockId }}</span>
                        </template>
                    </Column> -->

                    <Column field="receiptDate" header="Bill Date"></Column>
                    <Column field="billNo" header="Bill No"> </Column>
                    <Column field="expiryDate" header="Expiry Date"></Column>
                    <Column field="batchNo" header="Batch No"></Column>
                    <Column field="qty" header="Total Exp. Unit"></Column>

                    <!-- THIS IS SOUMIK CODE - Return Qty as integer -->
                    <Column field="tax3" header="Return Qty">
                        <template #body="{ data }">
                            <input 
                                type="number" 
                                v-model.number="data.tax3" 
                                class="form-control" 
                                style="width: 80px;"
                                min="0"
                                step="1"
                                :max="data.totalUnit"
                                @input="handleReturnQtyChange(data, $event)"
                                @focus="$event.target.select()"
                                :disabled="!isProductSelected(data)"
                            />
                        </template>
                    </Column>
                    <Column field="purchasePrice" header="Purchase Price"></Column>
                    <Column field="mrp" header="MRP"></Column>
                    <Column field="purchaseDisc" header="Discount"></Column>
                    <Column field="tax1" header="SGST"></Column>
                    <Column field="tax2" header="CGST"></Column>
                    <Column field="subTotal" header="Total"></Column>

                    <Column field="accountTitle" header="Supplier"></Column>
                  
                </DataTable>

                 <Button
                        type="submit"
                        label="Create Return Voucher"
                        icon="pi pi-search"
                        class="p-button-primary"
                        @click="returnVoucher"
                    />
            </div>


            <!-- This datatable is a computed one and invisible and used for printing purpose only-->
            <!--                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"-->
       

  <!-- THIS IS SOUMIK CODE - Improved Print Design -->
  <div id="invoiceArea" style="display: block; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
    <!-- Header Section -->
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2c3e50; padding-bottom: 15px;">
      <h1 style="margin: 0; color: #2c3e50; font-size: 28px; font-weight: bold;">AADYAMAA MEDICAL SHOP</h1>
      <h2 style="margin: 10px 0 5px 0; color: #e74c3c; font-size: 20px;">EXPIRY RETURN VOUCHER</h2>
      <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: left;">
          <p style="margin: 2px 0; font-size: 14px; color: #34495e;"><strong>Supplier:</strong> {{ searchFilters.customerName }}</p>
          <p style="margin: 2px 0; font-size: 14px; color: #34495e;"><strong>Date:</strong> {{ getCurrentFormattedDate() }}</p>
        </div>
        <div style="text-align: right;">
          <p style="margin: 2px 0; font-size: 14px; color: #34495e;"><strong>Voucher No:</strong> RV-{{ getCurrentFormattedDate().replace(/\//g, '') }}-001</p>
          <p style="margin: 2px 0; font-size: 14px; color: #34495e;"><strong>Total Items:</strong> {{ selectedProducts.length }}</p>
        </div>
      </div>
    </div>
    
    <!-- Items Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
      <thead>
        <tr style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: left; font-size: 12px; font-weight: bold;">PRODUCT NAME</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: center; font-size: 12px; font-weight: bold;">BATCH</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: center; font-size: 12px; font-weight: bold;">EXPIRY</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: center; font-size: 12px; font-weight: bold;">BILL NO</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: center; font-size: 12px; font-weight: bold;">BILL DATE</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: center; font-size: 12px; font-weight: bold;">QTY</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">PRICE</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">MRP</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">DISC%</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">SGST%</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">CGST%</th>
          <th style="border: 1px solid #2980b9; padding: 12px 8px; text-align: right; font-size: 12px; font-weight: bold;">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in selectedProducts" :key="item.id" 
            :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : '#ffffff', borderBottom: '1px solid #dee2e6' }">
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; font-size: 11px; max-width: 150px; word-wrap: break-word;">{{ item.itemName }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: center; font-size: 11px;">{{ item.batchNo }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: center; font-size: 11px;">{{ item.expiryDate }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: center; font-size: 11px;">{{ item.billNo }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: center; font-size: 11px;">{{ item.receiptDate }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: center; font-size: 11px; font-weight: bold; color: #e74c3c;">{{ Math.floor(item.tax3) }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px;">₹{{ parseFloat(item.purchasePrice).toFixed(2) }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px;">₹{{ parseFloat(item.mrp).toFixed(2) }}</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px;">{{ parseFloat(item.purchaseDisc).toFixed(1) }}%</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px;">{{ parseFloat(item.tax1).toFixed(1) }}%</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px;">{{ parseFloat(item.tax2).toFixed(1) }}%</td>
          <td style="border: 1px solid #dee2e6; padding: 10px 8px; text-align: right; font-size: 11px; font-weight: bold; color: #27ae60;">₹{{ parseFloat(item.subTotal).toFixed(2) }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr style="background: linear-gradient(135deg, #27ae60, #229954); color: white;">
          <td colspan="11" style="border: 1px solid #229954; padding: 12px 8px; text-align: right; font-size: 14px; font-weight: bold;">GRAND TOTAL:</td>
          <td style="border: 1px solid #229954; padding: 12px 8px; text-align: right; font-size: 14px; font-weight: bold;">₹{{ parseFloat(this.getRetTotal()).toFixed(2) }}</td>
        </tr>
      </tfoot>
    </table>

    <!-- Footer Section -->
    <div style="margin-top: 30px; border-top: 2px solid #2c3e50; padding-top: 15px;">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: left;">
          <p style="margin: 5px 0; font-size: 12px; color: #7f8c8d;"><strong>Prepared By:</strong> ________________</p>
          <p style="margin: 5px 0; font-size: 12px; color: #7f8c8d;"><strong>Signature:</strong></p>
        </div>
        <div style="text-align: right;">
          <p style="margin: 5px 0; font-size: 12px; color: #7f8c8d;"><strong>Authorized By:</strong> ________________</p>
          <p style="margin: 5px 0; font-size: 12px; color: #7f8c8d;"><strong>Signature:</strong></p>
        </div>
      </div>
      <div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #ecf0f1; border-radius: 5px;">
        <p style="margin: 0; font-size: 11px; color: #7f8c8d; font-style: italic;">This is a computer generated return voucher for expired medicines.</p>
        <p style="margin: 5px 0 0 0; font-size: 11px; color: #7f8c8d;">Generated on: {{ getCurrentFormattedDate() }} | Total Return Value: ₹{{ parseFloat(this.getRetTotal()).toFixed(2) }}</p>
      </div>
    </div>
  </div>

            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '50vw'}"
                :maximizable="true"
                position="top"
                class="p-fluid"
            >
                <template #header>
                    <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        <i class="pi pi-search" style="font-size: 1.2rem"></i>
                        {{ dialogTitle }}
                    </h5>
                </template>
                <div class="p-grid">
                    <div class="p-col">
                        <!-- <div class="p-field">
                            <label for="filterStore">Branch</label>
                            <Dropdown
                                v-model="searchFilters.storeID"
                                :options="filterBranch"
                                :filter="true"
                                optionLabel="name"
                                optionValue="id"
                            />
                        </div> -->
                        <div class="p-field">
                          <label for="from">Date From</label>
                         <input type="date" id="from"  v-model="searchFilters.date1" class="form-control">
                        </div>
                        <div class="p-field">
                          <label for="from">Date To</label>
                         <input type="date" id="to"  v-model="searchFilters.date2" class="form-control">
                        </div>
                        <div class="p-field">
                            <label for="type">Supplier</label>
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
import { computed, ref } from "vue";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";
import UserService from "../../service/UserService.js";
import AutoComplete from "primevue/autocomplete";
import ProfilerService from "../../service/ProfilerService.js";

   
  


interface IBranch {
    name: string;
}

interface IReport {
    productName: string;
    packSize: string;
    stripSize: string;
    batchNo: string;
    qty: string;
    expiryDate: string;
    minStock: string;
    branchDetails: IBranch;

    
}

interface CounterEntry {
  accountID: number;
  accountHead: string;
  amount: number;
  type: string;
}

@Options({
    title: 'Stock Expiry Report',
    components: {AutoComplete},
})


export default class StockExpiryReport extends mixins(UtilityOptions) {
    private userService;
    private userList = [];
    private profilerList = [];

    private profilerService;


    private dt = ref();
    private lists: IReport[] = [];
    private reportService;
    private resultTitle = "";
    private productDialog = false;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "Stock Expiry Report", to: "expiry-report" },
    ];

    private searchFilters = {
        id: "",
        storeID: 0,
        date1:"",
        date2:"",
        customerName:"",
        customerID:"",
    };
    private dialogTitle;
    private submitted = false;
    private filterBranch = [];
    private selectedProducts =[];
    private counterEntry: CounterEntry[] = [];

    // private counterEntry = [];




    //CALLING WHENEVER COMPONENT LOADS
    created() {
        this.reportService = new StoreReports();
        this.userService = new UserService();
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

    storeList() {
        this.reportService.getFilterList().then((res) => {
            this.filterBranch = res.stores;
        });
    }

    // USED TO GET SEARCHED ASSOCIATE
    loadList() {
        this.loading = true;
        this.reportService.stockExpiryReport(this.searchFilters).then((res) => {
            const data = this.camelizeKeys(res);
            console.log('Raw API Response:', res);
            console.log('Camelized Data:', data);
            console.log('First Record:', data.record[0]);
            this.resultTitle = data.resultTitle;
            this.lists = data.record.map(item => ({
                ...item,
                totalUnit2: 0,
                tax3: 0
            }));

            this.loading = false;
        });
        this.productDialog = false;
    }

    //used to create return voucher
    returnVoucher(){
        console.log('type of selectedProducts: '+typeof this.selectedProducts);
        
        // Check if products are selected
        if (!this.selectedProducts || this.selectedProducts.length === 0) {
            alert('Please select products first');
            return;
        }
        
        // Check if any return quantity is entered
        const hasReturnQty = this.selectedProducts.some(item => item.tax3 > 0);
        if (!hasReturnQty) {
            alert('Please enter return quantities for selected products');
            return;
        }
        
        let returnList = this.selectedProducts.filter(item => item.tax3 > 0);
        let supplierID = this.searchFilters.customerID;
        let total = this.getRetTotal();
        
        console.log('Total amount:', total);
        console.log('Supplier ID:', supplierID);
        console.log('Return List:', returnList);
        
        // Clear previous counter entries
        this.counterEntry = [];
        
        // Ensure total is valid
        if (!total || isNaN(total) || total <= 0) {
            alert('Invalid total amount. Please check return quantities.');
            return;
        }
        
        this.counterEntry.push({
            accountID: 2,
            accountHead: 'Expiry Return',
            amount: Number(total),
            type: "Debit",
        });
        
        console.log('Counter Entry:', JSON.stringify(this.counterEntry));
        
        //call to save the return voucher
       this.reportService.saveReturnVoucher(null, supplierID, total, returnList, this.counterEntry).then((res) => {
            //print the voucher
            var a = window.open('', '', 'height=500, width=500');
            a?.document.write(document.getElementById('invoiceArea')?.innerHTML);
           a?.print();
            });
    }

    

     onCellEditComplete = (event) => {
        
        let { data, newValue, field, index } = event;
        console.log("Data is "+JSON.stringify(data));
        
        // Check if this is Return Qty field
        if (field === 'tax3') {
            // Check if this product is selected (checkbox checked)
            const isProductSelected = this.selectedProducts.some(selected => selected.id === data.id);
            
            if (!isProductSelected) {
                // Product not selected, don't allow editing Return Qty
                alert('Please select the product first to enter return quantity');
                data[field] = 0; // Reset to 0
                return;
            }
            //alert(data.qty+'...'+newValue);
            // THIS IS SOUMIK CODE - Validate return quantity as integer
            const intValue = Math.floor(Number(newValue)) || 0;
            if (intValue > data.qty) {
                alert('Return quantity cannot exceed total units');
                 data[field] = 0;
                 newValue = 0;
            } else {
                newValue = intValue;
            }
        }
        
        console.log("new value "+newValue);
        data[field]=newValue;
        
        // Update selected products if this product is selected
        const selectedIndex = this.selectedProducts.findIndex(selected => selected.id === data.id);
        
        if (selectedIndex !== -1) {
            this.selectedProducts[selectedIndex][field] = newValue;
            
            // Recalculate total if needed
            if (field === 'tax3') {
                let pp = this.selectedProducts[selectedIndex]['purchasePrice'] || 0;
                let qty = newValue;
                let tax = this.selectedProducts[selectedIndex]['tax1'] || 0;
                let totalPrice = (pp * qty) * ((100 + 2 * tax) / 100);
                this.selectedProducts[selectedIndex]['subTotal'] = Math.round(totalPrice);
            }
        }
        
        this.rList[index][field] = newValue;
}

    get rList() {
        const l: IReport[] = [];

        this.lists.forEach((e) => {
           // e.expiryDate = this.formatMonthDate(e.expiryDate);
            l.push(e);
        });

        return l;
    }

    searchProfiler(event) {
    setTimeout(() => {
      this.profilerService.searchSuppliers(event.query.trim()).then((data) => {
        this.profilerList = data.records;
      });
    }, 200);
  }

  saveProfile(event) {
    const profileInfo = event.value;
    this.searchFilters.customerName = profileInfo.account_title;
    this.searchFilters.customerID = profileInfo.id;
  }


   isProductSelected(data) {
        return this.selectedProducts.some(selected => selected.id === data.id);
    }

    // THIS IS SOUMIK CODE - Handle return qty as integer
    handleReturnQtyChange(data, event) {
        const value = Math.floor(Number(event.target.value)) || 0;
        
        // Instant validation - Check if exceeds total units
        if (value > data.totalUnit) {
            // Show error popup
            alert(`Return quantity not acceptable! Total unit is ${data.totalUnit}, you entered ${value}`);
            
            // Reset to maximum allowed value
            data.tax3 = Math.floor(data.totalUnit);
            event.target.value = Math.floor(data.totalUnit);
            
            // Force focus back to input to show the corrected value
            setTimeout(() => {
                event.target.focus();
                event.target.select();
            }, 100);
            
            return;
        }
        
        // If value is valid, update the data as integer
        data.tax3 = value;
        
        // Update selected products array
        const selectedIndex = this.selectedProducts.findIndex(selected => selected.id === data.id);
        
        if (selectedIndex !== -1) {
            this.selectedProducts[selectedIndex].tax3 = value;
            
            // Recalculate and round the subTotal
            let pp = this.selectedProducts[selectedIndex]['purchasePrice'] || 0;
            let tax = this.selectedProducts[selectedIndex]['tax1'] || 0;
            let totalPrice = (pp * value) * ((100 + 2 * tax) / 100);
            this.selectedProducts[selectedIndex]['subTotal'] = Math.round(totalPrice);
        }
    }

   getCurrentFormattedDate() {
        const now = new Date();
        const day = now.getDate();
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        
        return `${day}${month}-${year}`;
    }

   getRetTotal(){
    let total = 0;
    if(this.selectedProducts != null && this.selectedProducts.length > 0) {
        console.log('Selected Products Count:', this.selectedProducts.length);
        
        this.selectedProducts.forEach((item, index) => {
            // Only count items with return quantity > 0
            if(item.tax3 > 0) {
                console.log('Item:', item.itemName, 'Return Qty:', item.tax3, 'SubTotal:', item.subTotal);
                total += Number(item.subTotal) || 0;
            }
        });
    }
    
    console.log('Total Return Amount:', total);
    return Math.round(total);
   }
}



</script>

