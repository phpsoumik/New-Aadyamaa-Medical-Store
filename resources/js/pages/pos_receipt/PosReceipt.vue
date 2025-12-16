
<template>

  <Dialog 
      v-model:visible="isDialogOpen" 
      modal 
      header="You selected" 
      :closable=true
      :style="{ width: '50rem' }"

    >
      <p style="font-size: 15px;">
        <strong>{{ selectedItem.product_name }}</strong>
        </p>
      <DataTable :value="selectedItem.variations" tableStyle="min-width: 45rem"   
        v-model:selection="variation"  
        selectionMode="single" 
        @row-select="saveItem(variation,placeIndex)"
        ref="myDataTable"
      >

       <template #row-id="{ index }">
             <span>first-data-row</span>
      </template>
          <Column field="batch_no" header="Batch"></Column>
          <Column field="" header="Expiry">
          <template #body="{data}">
            {{ formatExpiryDate(data.expiry_date) }}
          </template>
          </Column>
          <Column field="qty" header="Total Qty"></Column>

          <Column field="" header="Pata/Loose">
          <template #body="{ data }">
            {{ Math.floor(data.qty/data.strip_size) }}:{{data.qty%data.strip_size}}
          </template>
          </Column>
          <Column field="sale_price" header="Price"></Column>
      </DataTable>


        <!-- <ul role="listbox">
              <li class="p-highlight" role="option" v-for="variation in selectedItem.variations" :key="variation.id" @click="saveItem(variation,placeIndex)" autofocus="true">
                <b>Batch:</b> {{ variation.batch_no }} |<b>&nbsp;Exp:</b> {{ formatExpiryDate(variation.expiry_date) }} |&nbsp;<b>PSize:</b> {{variation.strip_size}} |&nbsp;<b>Qty:</b> {{ variation.qty }} |&nbsp;<b>Price:</b> {{ variation.sale_price }}
              </li>
        </ul> -->
      <template #footer>
          <Button label="Close" icon="pi pi-check" @click="isDialogOpen = false" autofocus />
      </template>
    </Dialog>
  <fullscreen v-model:fullscreen="screenState.fullscreen" :teleport="screenState.teleport"
    :page-only="screenState.pageOnly">
    <section>
      <div style="height: 90vh;">
        <div style="height: 0.5vh; background-color:#ccc;">
          <ProgressBar v-if="progressBar" mode="indeterminate" style="height: 0.2em" />
        </div>
        <Toolbar style="height: 7.5vh">
          <template #start>
            <h5 class="p-mt-2">
              <b>
                <i style="font-size: 20px" class="pi pi-globe"></i> {{ storeName }}
              </b>
            </h5>
          </template>
          <template #end>
            <div class="p-mx-1">
              <Dropdown style="width: 10rem" v-model="item.type" :options="modeList" optionLabel="label"
                optionValue="value" @change="clearFreeUnit()" />
            </div>
            <div class="p-mx-1">
              <Button icon="pi pi-plus" label="New Row" class="p-button-success" @click="openNewRow" />
            </div>
            <div class="p-mx-1">
              <Button icon="pi pi-plus" label="New Profile" class="p-button-success" @click="openProfileDialog" />
            </div>
            <div class="p-mx-1">
              <Button icon="pi pi-times" label="Clear Screen" class="p-button-danger" @click="clearAll" />
            </div>
            <div class="p-mx-1">
              <Button v-if="!screenState.fullscreen" icon="pi pi-window-maximize" class="p-button-warning"
                @click="toggleScreen" />
              <Button v-if="screenState.fullscreen" icon="pi pi-window-minimize" class="p-button-warning"
                @click="toggleScreen" />
            </div>
          </template>
        </Toolbar>
        <div class="p-grid p-m-0">
          <div class="p-col-9">

            <div class="pos-table" style="height: 72.7vh">
              <table class="table table-stiped table-bordered p-m-0">
                <thead>
                  <tr class="pos-heading">
                    <th style="width: 18rem">Item Name</th>
                    <th>LeafPata</th>
                    <th style="background: red;width: 8rem">Unit</th>
                    <th v-if="item.type == 'TRN'">Gift Unit</th>
                    <th style="width: 5rem">Price ({{ currency }})</th>
                    <th>Expiry</th>
                    <th style="background: red;">Margin (%)</th>
                    <th style="background: green; width: 5rem">Discount (%)</th>
                    <th>Subtotal ({{ currency }})</th>
                    <th>REMOVE</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(savedItem, index) in savedItemList" :key="savedItem">
                    <tr class="table-row">
                       
 
                      <td style="width:18rem">

           
                    
                        <AutoComplete ref="medicineSearch" :delay="1000"  :minLength="2" @key=index showClear="true"
                          scrollHeight="500px" v-model="savedItem.productName" :suggestions="itemList" id="ac" 
                          placeholder=" SCAN BARCODE OR SEARCH ITEMS" @complete="searchItem($event)" 
                          autoFocus inputStyle="width:18rem" @item-select="showVarations($event, index)">
                          <template #item="slotProps">
                            <ul>
                                <li :class="{ 'requested-item': slotProps.item.is_requested }">
                                  <span v-if="slotProps.item.is_requested" class="requested-badge">🔔 REQUESTED</span>
                                  <b>{{ slotProps.item.product_name.toUpperCase() }}</b>:-{{ Math.floor(slotProps.item.totalQty/slotProps.item.strip_size) }}:{{slotProps.item.totalQty%slotProps.item.strip_size}}
                                  <div v-if="slotProps.item.is_requested" class="requested-customer-info">
                                    <small>Customer: {{ slotProps.item.customer_name }} | ₹{{ slotProps.item.advance_payment }} Advance</small>
                                  </div>
                                </li>
                              </ul>
                          </template>
                        </AutoComplete>
                      </td>
                      
                      <td>
                        <!-- <select v-model="savedItem.mode" @change="setPricing(savedItem)" class="apply-style">
                          <option v-for="t in unitType" :key="t" :value="t">
                            {{ t }}
                          </option>
                        </select> -->
                        <InputNumber :useGrouping="false" style="width: 8rem; height: 35px" :min="0"
                             v-model="savedItem.leaf" :id="'leafcomp' + index" class="p-p-0 p-m-0" @keyup="handleSK($event)" />
                      </td>
                      <td>
                        <div class="p-inputgroup">
                          <!-- <Button
                            icon="pi pi-plus"
                            class="p-button-success p-p-0"
                            @click="adjustQty('add', savedItem)"
                          /> -->
                         
                            <InputNumber :useGrouping="false" style="width: 8rem; height: 35px" :min="0"
                            v-model="savedItem.unit" class="p-p-0 p-m-0" @keyup="handleSK($event)" />
                          <!-- <Button
                            icon="pi pi-minus"
                            class="p-button-warning p-p-0"
                            @click="adjustQty('minus', savedItem)"
                          /> -->
                        </div>
                      </td>
                      <td v-if="item.type == 'TRN'">
                        <div class="p-inputgroup">
                          <Button icon="pi pi-plus" class="p-button-success p-p-0"
                            @click="adjustFreeQty('add', savedItem)" />
                          <InputNumber :useGrouping="false" style="width: 2rem; height: 35px" :min="1"
                            v-model="savedItem.freeUnit" class="p-p-0 p-m-0" />
                          <Button icon="pi pi-minus" class="p-button-warning p-p-0"
                            @click="adjustFreeQty('minus', savedItem)" />
                        </div>
                      </td>
                      <td class="p-pt-2">
                        {{ currency }} {{ fixDigits(savedItem.sellingPrice) }}
                      </td>
                      <td class="p-pt-2">
                        {{ formatExpiryDate(savedItem.expiryDate) }}
                      </td>
                      <td class="p-pt-2">
                        {{ savedItem.itemDescription }}
                      </td>
                      <td class="p-inputgroup">
                        <!-- {{ fixDigits(savedItem.itemDisc) }} -->
                        <InputNumber :useGrouping="false" style=" width:5rem;height: 35px" :min="0"
                          v-model="savedItem.itemDisc" inputId="minmaxfraction" :minFractionDigits="0"
                          :maxFractionDigits="2" class="p-p-0 p-m-0"  id="cd"/>
                      </td>
                      <td class="p-pt-2">{{ getTheSubtotal(savedItem) }}</td>
                      <td class="p-text-center">
                        <Button icon="pi pi-minus" class="p-button-danger p-p-1" @click="clearListItem(savedItem)" />
                      </td>
                    </tr>
                    <tr class="item-detail-row">
                      <td :colspan="item.type == 'TRN' ? 9 : 8">
                        <span class="p-mr-1">
                          GENERIC:
                          <span style="color: #fff; background-color: #c00">
                            {{ limitString(savedItem.generic) }}
                          </span></span>
                        <span class="p-mx-1">
                          BATCH NO : {{ savedItem.batchNo }}
                        </span>
                        <span class="p-mx-1">
                          PACK/STRIP SIZE : {{ savedItem.sheetSize }}
                        </span>
                        <span class="p-mx-1">
                          NO OF PACKS/STRIPS : {{ savedItem.packSize }}
                        </span>
                        <span class="p-mx-1" v-if="taxNames[0].show">
                          {{ taxNames[0].taxName }} :
                          {{ fixDigits(savedItem.tax1) }}%
                        </span>
                        <span class="p-mx-1" v-if="taxNames[1].show">
                          {{ taxNames[1].taxName }} :
                          {{ fixDigits(savedItem.tax2) }}%
                        </span>
                        <span class="p-mx-1" v-if="taxNames[2].show">
                          {{ taxNames[2].taxName }} :
                          {{ fixDigits(savedItem.tax3) }}%
                        </span>
                        <span class="p-mx-1">
                          TOTAL UNITS : {{ fixDigits(savedItem.totalUnit) }}
                        </span>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
          <div class="p-col-3" style="border-left: 1px solid #eee">
            <div class="p-fluid">
              <div class="p-field">
                <AutoComplete :delay="1000" :minLength="3" @item-select="saveProfile($event)" scrollHeight="500px"
                  v-model="v$.selectedProfile.$model" :suggestions="profilerList" placeholder="Search Profile"
                  @complete="searchProfiler($event)" :dropdown="false" onclick="this.value=''" @keyup="setPhone($event)">
">
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
                <span v-if="v$.selectedProfile.$error && submitted">
                  <span id="p-error" v-for="(error, index) of v$.selectedProfile.$errors" :key="index">
                    <small class="p-error">{{ error.$message }}</small>
                  </span>
                </span>
                <small v-else-if="(v$.selectedProfile.$invalid && submitted) ||
                  v$.selectedProfile.$pending.$response
                  " class="p-error">{{
    v$.selectedProfile.required.$message.replace(
      "Value",
      "Profile"
    )
  }}</small>
              </div>
            </div>
            <div class="p-fluid" v-if="item.type == 'TRN'">
              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Choose Transfer Store
                </label>
                <Dropdown v-model="v$.transferStoreID.$model" :filter="true" :options="storeList" optionLabel="name"
                  optionValue="id" placeholder="Search Store" />
                <span v-if="v$.transferStoreID.$error && submitted">
                  <span id="p-error" v-for="(error, index) of v$.transferStoreID.$errors" :key="index">
                    <small class="p-error">{{ error.$message }}</small>
                  </span>
                </span>
                <small v-else-if="(v$.transferStoreID.$invalid && submitted) ||
                  v$.transferStoreID.$pending.$response
                  " class="p-error">{{
    v$.transferStoreID.required.$message.replace(
      "Value",
      "Transfer Store"
    )
  }}</small>
              </div>
            </div>
            <div class="p-fluid" v-if="item.type != 'TRN'">
              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Patient Name
                </label>
                <InputText v-model="item.patientDetails" placeholder="e.g A Ghosh" />
              </div>
            </div>
            <div class="p-fluid" v-if="item.type != 'TRN'">
              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Doctor Name
                </label>
                <InputText v-model="item.doctorDetails" placeholder="e.g Dr A Ghosh" />
              </div>
            </div>
            <div class="p-fluid">
              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Patient Phone
                </label>
                <InputText v-model="item.description" id="patientPhone" />
              </div>


            </div>
            <div class="p-fluid">
              <div class="p-field">
                <label for="address">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Patient Address
                </label>
                <InputText v-model="item.address" id="address" />
              </div>


            </div>
            <!--sam-->

            <div class="p-fluid">
              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Overall Bill Discount(%)
                </label>
                <InputText v-model="item.discount" @keyup="calculateDiscount(item.discount)"/>
              </div>

              <div class="p-field">
                <label for="description">
                  <i style="font-size: 12px; color: #11467e" class="pi pi-file"></i>
                  Bill Adjustment
                </label>
                <InputText v-model="this.billAdjustment" />
              </div>
              <!-- <Button class="p-col b-style-home" icon="pi pi-check" label="APPLY"
                @click="calculateDiscount(item.discount)" /> -->



            </div>
            <!--end sam-->
            <div class="p-fluid">
              <div class="p-field">
                <label for="description">Taxes Includes</label>
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon" v-if="taxNames[0].show">
                    <Checkbox :disabled="taxNames[0].optionalReq == 'Required'" v-model="selectedTaxes.tax1"
                      :binary="true" />
                    <span class="p-mx-2">{{ taxNames[0].taxName }}</span>
                  </span>
                  <span class="p-inputgroup-addon" v-if="taxNames[1].show">
                    <Checkbox :disabled="taxNames[1].optionalReq == 'Required'" v-model="selectedTaxes.tax2"
                      :binary="true" />
                    <span class="p-mx-2">{{ taxNames[1].taxName }}</span>
                  </span>
                  <span class="p-inputgroup-addon" v-if="taxNames[2].show">
                    <Checkbox :disabled="taxNames[2].optionalReq == 'Required'" v-model="selectedTaxes.tax3"
                      :binary="true" />
                    <span class="p-mx-2">{{ taxNames[2].taxName }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div style="height: 10vh" class="p-grid p-m-0 p-p-0">
        <Button class="p-col p-button-success b-style" icon="pi pi-home" label="HOME" @click="redirectHome()" />
        <span class="set-bottom-amt p-col">Total QTY <br />
          # {{ savedItemList.length }}</span>
        <span class="set-bottom-amt p-col">Total Gross <br />
          {{ currency }} {{ fixDigits(totalGross) }}</span>
        <span class="set-bottom-amt p-col">Total Disc <br />
          {{ currency }} {{ fixDigits(totalDiscAmount) }}</span>
        <span class="set-bottom-amt p-col" v-if="taxNames[0].show">Total {{ taxNames[0].taxName }} <br />
          {{ currency }} {{ fixDigits(totalTax1) }}</span>
        <span class="set-bottom-amt p-col" v-if="taxNames[1].show">Total {{ taxNames[1].taxName }} <br />
          {{ currency }} {{ fixDigits(totalTax2) }}</span>
        <span class="set-bottom-amt p-col" v-if="taxNames[2].show">Total {{ taxNames[2].taxName }} <br />
          {{ currency }} {{ fixDigits(totalTax3) }}</span>
        <span class="set-bottom-amt p-col">Net Total <br />
          {{ currency }} {{ fixDigits(netTotal) }}</span>
        <Button v-if="item.type != 'TRN'" class="p-col p-button-warning b-style" icon="pi pi-arrow-right" label="NEXT"
          @click="openPaymentMethod(!v$.$invalid)" :disabled="item.profileID == 0 || netTotal <= 0" />
        <Button v-else class="p-col p-button-warning b-style" icon="pi pi-arrow-right" label="DONE"
          @click="getProceededPayments([])" :disabled="item.profileID == 0 || netTotal <= 0" />
      </div>
    </section>
    <Dialog v-model:visible="refundReceiptDialog" :style="{ width: '600px' }" header="Search Receipt" position="top">
      <div class="confirmation-content">
        <i class="pi pi-search p-mr-3" style="font-size: 2rem" />
        <div class="p-fluid" v-if="item.type != 'TRN'">
          <div class="p-field">
            <label for="search_receipt"> Enter Receipt No </label>
            <InputText id="search_receipt" autoFocus v-model="item.searchReceiptNo"
              placeholder="e.g INE-02502100000000" />
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" class="p-button-text" @click="refundReceiptDialog = false" />
        <Button label="Search" icon="pi pi-search" class="p-button-success" :disabled="item.searchReceiptNo == ''"
          @click="fetchReceiptNo()" />
      </template>
    </Dialog>
  </fullscreen>
  <PaymentScreen :receiptDetail="{
    dialogStatus: paymentDialog,
    itemSource: item.type,
    restriction: 'No',
    dialogTilte: dialogTitle,
    customerID: this.item.profileID,
    customerName: this.state.selectedProfile,
    closeConfirmation: true,
  }" v-on:closePaymentScreenEvent="closePaymentScreen" v-on:getProceededPaymentsEvent="getProceededPayments" />


  <ProfilerDialog :profilerDetail="{
    status: this.profileStatus,
    profilerID: 0,
    statusType: this.statusType,
    dialogTitle: this.dialogTitle,
    currentUserID: this.currentUserID,
  }" v-on:updateProfilerStatus="updateProfilerStatus" />



<div style="background-color: rgb(74, 52, 140); width: 2.28in; display: none" id="invoiceArea">
  <div>
            
            <div style="text-align;:center">
                      <p style="font-weight:bold;font-size: 14px;text-align: center;">ADDYAMA MEDICAL STORES</p>
                      <p style="font-size: 14px;text-align: center;margin-bottom: -1em;">DASGHARA, HOOGHY </p>
                      <p style="font-size: 14px;text-align: center;margin-bottom: -1em;" >Contact - 8670320341</p>
                      <p style="font-size: 14px;text-align:center;">DL NO: WB/HGL/BIO/R/713808</p>
                      <p style="font-size: 14px;text-align:center;">GSTIN: 19BNQPG2404J1ZV</p>



                      <!-- <h2>Bill No: #<%= order_number_format %></h2> -->

                </div>

                <div>Bill No:#<span id="billNo"></span></div>
                <div> Date: <span id="rDate">{{ currentDate() }}</span> </div>
                <div>Customer Name : <span>{{ item.patientDetails }}</span></div>
                <div>Customer Phone : <span>{{ item.description }}</span></div>
                <div>Customer Address : <span>{{ item.address }}</span></div>


                

            </div>
            <hr style="  border-top: 1px solid red;"/>
        <table style="width:2.28in">
          <tr>
              <td style="font-size:12px;" colspan=2>Item</td>
              <td style="font-size:12px;">MRP</td>
              <td style="font-size:12px;">Qty</td>
              <td style="font-size:12px;" colspan=2>Our Price</td>
          </tr>
          <tr>
            <td colspan="6"><hr style="border-top: 1px dashed black;"></td>
          </tr>
          <tr  v-for="(item, index) in savedItemList" :key="item">


                <td colspan=2>
                    <p style="font-size:0.7em">{{ item.productName }}</p>

                </td>
                <td >
                    <p style="font-size:0.7em">{{ item.mrp.toFixed(2) }}</p>
                </td>
                <td >
                    <p style="font-size:0.7em">{{ item.leaf }}.{{ item.unit }}</p>
                </td>
                <td ><p style="font-size:0.7em">{{ item.subTotal.toFixed(2) }}</p></td>
          </tr>
          <tr>
            <td colspan="6"><hr></td>
          </tr>
          <tr>
            <td colspan="2">TOTAL</td>
            <td>---</td>
            <td></td>

            <td colspan="2">{{netTotal.toFixed(2)}}</td>
          </tr>
        </table>
       <hr style="  border-top: 1px solid red;"/>

        <div>Thank you for shopping with us</div>
    </div>
</template>



<script lang="ts">
import { Options, Vue } from "vue-class-component";
import PosService from "../../service/PosService.js";
import ProfilerService from "../../service/ProfilerService.js";
import ChartService from "../../service/ChartService.js";
import useVuelidate from "@vuelidate/core";
import { required, requiredIf, helpers } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import AutoComplete from "primevue/autocomplete";

import SearchFilter from "../../components/SearchFilter.vue";
import PreviewAccountingReceipt from "../../components/PreviewAccountingReceipt.vue";
import { ref, defineComponent, toRefs, reactive } from "vue";
import PaymentScreen from "../../components/PaymentScreen.vue";
import { useStore, ActionTypes } from "../../store";
import ProfilerDialog from "../../components/ProfilerDialog.vue";
import { ItemList } from "../pos_receipt/IPosReceipt";
import router from "../../router";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';




interface PaymentListType {
  paymentType: string;
  accountNo: string;
  terminalId: string;
  authCode: string;
  transId: string;
  transStatus: string;
  transType: string;
  transDate: string;
  transTime: string;
  transAmount: number;
  transTotalAmount: number;
  transRef: string;
  entryMode: string;
  hostResponse: string;
  giftCardRef: string;
  cardBalance: string;
  tendered: number;
  change: number;
  roundOff: number;
}

interface CounterEntry {
  accountID: number;
  accountHead: string;
  amount: number;
  type: string;
}

@Options({
  title: 'Orders',
  components: {
    AutoComplete,
    SearchFilter,
    PreviewAccountingReceipt,
    PaymentScreen,
    ProfilerDialog,
  },
})

// State for the dialog
//

export default class PosReceipt extends Vue {
  private modeList = [
    { label: "SALE", value: "INE" },
    { label: "REFUND", value: "RFD" },
    { label: "MODIFY", value: "MDF" },
    { label: "TRANSFER", value: "TRN" },
  ];

  private isDialogOpen = false;
  private selectedItem;
  private placeIndex;
  // private savedItem.productName;

  private unitType = ["Select Unit", "Strip", "Unit"];
  // private unitType = ["Select Unit", "Full", "Loose"];
  private variation = ref();
 

  private paymentDialog = false;
  private profileStatus = false;
  private statusType = "New";
  private storeName = "Loading...";
  private refundReceiptDialog = false;
  private submitted = false;
  private defaultCustomerName = "";
  private defaultCustomerID = 0;
  private currentUserID = 0;
  private itemScanBox = "";
  private dialogTitle = "";
  private screenMode = "minimize";
  private profilerService;
  private posService;
  private toast;
  private storeList = [];
  private profilerList = [];
  private itemList = [];
  private store = useStore();
  private billDiscount = 0;
  private billAdjustment = 0;
  
  private counterEntry: CounterEntry[] = [];

  private screenState = reactive({
    fullscreen: false,
    teleport: true,
    pageOnly: false,
  });

  private selectedTaxes = {
    tax1: false,
    tax2: false,
    tax3: false,
  };

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

  private state = reactive({
    selectedProfile: "",
    transferStoreID: "",
  });

  private savedItemList: ItemList[] = [];

  private validationRules = {
    selectedProfile: {
      required,
    },
    transferStoreID: {
      required: helpers.withMessage(
        "Transfer Store is required",
        requiredIf(() => {
          return this.item.type == "TRN";
        })
      ),
    },
  };

  private paymentList: PaymentListType[] = [];

  private item = {
    id: 0,
    profileID: this.defaultCustomerID,
    discount: 0,
    totalPaid: 0,
    totalTendered: 0,
    totalChange: 0,
    totalGrossAmt: 0,
    totalCost: 0,
    totalBill: 0,
    totalTax1: 0,
    totalTax2: 0,
    totalTax3: 0,
    totalTax: 0,
    description: "",
    paymentMethod: "",
    doctorDetails: "",
    patientDetails: "",
    searchReceiptNo: "",
    status: "Active",
    type: "INE",
  };

  private v$ = useVuelidate(this.validationRules, this.state);

  //DEFAULT METHOD OF TYPE SCRIPT
  created() {
    this.profilerService = new ProfilerService();
    this.posService = new PosService();
    this.toast = new Toaster();
  }

 

  mounted() {
    //alert('mounted');
    console.log('Route query are '+this.$route.query.rn);
    let rNo=this.$route.query.rn;
    //alert(rNo);
    if(rNo!=null && rNo!='undefined'){
     this.item.searchReceiptNo=rNo;
     this.fetchReceiptNo();
     this.item.type="MDF";

    }else{
      for (var i = 0; i < 1; i++) {
      this.openNewRow();
       }
    }
   
    this.loadList();
  

  }

  currentDate() {
      //alert('current date');
      const current = new Date();
      const date = `${current.getDate()}/${current.getMonth()+1}/${current.getFullYear()}`;
      return date;
    
  }

  toggleScreen() {
    this.screenState.fullscreen = !this.screenState.fullscreen;
  }

  get progressBar() {
    return this.store.getters.getProgressBar;
  }

  searchProfiler(event) {
    setTimeout(() => {
      this.profilerService.searchProfiler(event.query.trim()).then((data) => {
        this.profilerList = data.records;
      });
    }, 200);
  }

  searchItem(event) {
    setTimeout(() => {
      this.posService.searchItem(event.query.trim()).then((data) => {
        this.itemList = data.records;
       //this.itemList = this.filterItems(data.records);
        console.log(">>>RETURNED STOCKS BY SEARCH "+JSON.stringify(this.itemList));
        console.log(">>>CHECKING REQUESTED ITEMS:");
        this.itemList.forEach(item => {
          if(item.is_requested) {
            console.log("REQUESTED ITEM FOUND:", item.product_name, "Customer:", item.customer_name, "Advance:", item.advance_payment);
          }
        });
      });
    }, 200);
  }
 
 
  filterItems(records) {
    
    records= records.filter((item) => {
      return (item.qty>0);
    })
    
    return records;
  }
  saveProfile(event) {
   // alert("save profile");
    const profileInfo = event.value;
    this.state.selectedProfile = profileInfo.account_title;
    this.item.profileID = profileInfo.id;
    this.item.patientDetails=profileInfo.account_title;
    this.item.description=profileInfo.contact_no;
  }


  //sam
  handleSK(e) {

    if (e.key == 'Enter') {
      //alert("enter pressed");
      this.openPaymentMethod(true);
    }
  }

  setPhone(e){
    // alert("set phone");
    // alert(e.target.value);
     document.getElementById("patientPhone").value=e.target.value;
     this.item.description=e.target.value;
  }

  showVarations(event, index){

       // event.preventDefault();
    //document.getElementById(variationId).style.display="block";
    //alert('enter key' + variationId);
     const itemInfo = event.value;
    //alert("variations "+JSON.stringify(itemInfo.variations));

     // this.selectedFruit.value = itemInfo.product_name;
     this.selectedItem=itemInfo;
     this.placeIndex = index;

     //event.value=null;
    // Open the dialog
    this.isDialogOpen = true;

    
    const ac = this.$refs.medicineSearch;

// const tabEvent = new KeyboardEvent('keydown', {
//     key: 'Tab',
//     keyCode: 9,   // Deprecated, but good for maximum compatibility
//     which: 9,     // Deprecated, but good for maximum compatibility
//     bubbles: true,      // Allows the event to bubble up the DOM tree
//     cancelable: true    // Allows the default browser action to be potentially triggered
//   });

  // 2. Dispatch the event on the element
  //const dispatched = this.dispatchEvent(tabEvent);



  }

  onDialogHide(){
    //alert('dialog hide' +this.selectedItem.product_name);
    this.itemScanBox = "";
    document.getElementById('ac').value="";
    //savedItem.productName=null;
    //this.savedItemList=[];
  }

 
  saveItem(event, index) {
   const itemInfo = event;
     //const itemInfo = event.value;
    // alert("savedf item "+JSON.stringify(itemInfo.variations));
    //alert(itemInfo);
    //const itemInfo = selectedVariation;
    let sellRate = 0;
    let disc = 0;

   //alert("VARIATIONS OF SELECTED PRODUCTS>>>"+JSON.stringify(itemInfo));
    if (this.item.type != "TRN") {
      sellRate = itemInfo.sale_price;
      disc = itemInfo.discount_percentage;
    } else {
      sellRate = itemInfo.purchase_price;
      disc = 0;
    }

    this.savedItemList[index] = {
      mode: "Strip",
      stockID: itemInfo.id,
      productName: itemInfo.product_name,
      generic: itemInfo.generic,
      itemDescription: itemInfo.description,
      leaf:0,
      unit: 0,
      totalUnit: 0,
      stockQty: Number(itemInfo.qty),
      freeUnit: 0,
      supplierBonus: 0,
      batchNo: itemInfo.batch_no,
      packSize: Number(itemInfo.pack_size),
      sheetSize: Number(itemInfo.strip_size),
      purchasePrice: Number(itemInfo.purchase_price),
      originalPPrice: Number(itemInfo.purchase_price),
      originalSPrice: Number(sellRate),
      sellingPrice: Number(sellRate),
      mrp: Number(itemInfo.mrp),
      brandName: itemInfo.bName,
      sectorName: itemInfo.bSector,
      categoryName: itemInfo.cName,
      productType: itemInfo.pType,
      expiryDate: itemInfo.expiry_date,
      itemDisc: Number(disc),
      tax1: Number(itemInfo.tax_1),
      tax2: Number(itemInfo.tax_2),
      tax3: Number(itemInfo.tax_3),
      subTotal: 0,
    };

    this.itemScanBox = "";
   document.getElementById("leafComp0")?.focus();
  // alert(this.$refs.leafcomp0.innerHTML);
    
// document.activeElement.dispatchEvent(new KeyboardEvent("keypress", { 
//     key: "Tab" 
    
// }));
    //open another row
    console.log("adding another row");
    this.isDialogOpen=false;

   this.openNewRow();
  }

  openNewRow() {
    this.savedItemList.push({
      mode: "Strip",
      stockID: 0,
      productName: '',
      generic: '',
      itemDescription: '',
      leaf:0,
      unit: 0,
      totalUnit: 0,
      stockQty: 0,
      freeUnit: 0,
      supplierBonus: 0,
      batchNo: '',
      packSize: 0,
      sheetSize: 0,
      purchasePrice: 0,
      originalPPrice: 0,
      originalSPrice: 0,
      sellingPrice: 0,
      mrp: 0,
      brandName: '',
      sectorName: '',
      categoryName: '',
      productType: '',
      expiryDate: '',
      itemDisc: 0,
      tax1: 0,
      tax2: 0,
      tax3: 0,
      subTotal: 0,
    });

    this.itemScanBox = "";
  }


  loadList() {
    this.posService.getItems().then((data) => {
      // //taxNames
      this.taxNames = [];

      this.storeList = data.stores;

      this.taxNames.push({
        taxName: data.storeTaxes[0].tax_name_1,
        show: data.storeTaxes[0].show_1 == "true" ? true : false,
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
        show: data.storeTaxes[0].show_2 == "true" ? true : false,
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
        show: data.storeTaxes[0].show_3 == "true" ? true : false,
        optionalReq: data.storeTaxes[0].required_optional_3,
        taxValue:
          data.storeTaxes[0].show_3 == "true"
            ? Number(data.storeTaxes[0].tax_value_3)
            : 0,
        accountHead: data.storeTaxes[0].tax_name3.chartName,
        accountID: data.storeTaxes[0].link3,
      });

      if (
        this.taxNames[0].optionalReq == "Required" &&
        this.taxNames[0].show == true
      ) {
        this.selectedTaxes.tax1 = true;
      }

      if (
        this.taxNames[1].optionalReq == "Required" &&
        this.taxNames[1].show == true
      ) {
        this.selectedTaxes.tax2 = true;
      }

      if (
        this.taxNames[2].optionalReq == "Required" &&
        this.taxNames[2].show == true
      ) {
        this.selectedTaxes.tax3 = true;
      }

      //Default Customer
      this.state.selectedProfile = data.defaultCustomer.account_title;
      this.item.profileID = data.defaultCustomer.id;
      this.defaultCustomerName = data.defaultCustomer.account_title;
      this.defaultCustomerID = data.defaultCustomer.id;
      this.currentUserID = data.currentUserID;
      this.storeName = data.storeName;
    });
  }

  getTheSubtotal(data) {
    console.log('custom get the sub total ' +JSON.stringify(data));
    let total = 0;
     let netTotal=0;
     const type = data.productType;
    //  console.log('product type ' + type);
     console.log('stock qty ' + data.stockQty);
     if (data.stockID!=0) {

    let tUnits = this.sumUnitTotalQty(data);
   // let tUnits=60;
    console.log("tunits "+tUnits);
   // let tUnits=1;
 
     if (data.stockQty < tUnits && this.item.type == "INE") {
      //  data.unit = 0;
      //  data.mode = "Strip";
      //  data.freeUnit = 0;
       //this.toast.showError("This Item does not have enough stock qty for pack");
       alert("Not enough stock");
     }else{
 
     
     const price = data.sellingPrice;
     console.log(' price is ' + price);
     const unitPrice = price/data.sheetSize;
     const discount = Number(data.itemDisc);
     console.log("item discount >>>>"+discount);
     
 
     const tax1 = this.selectedTaxes.tax1 == true ? Number(data.tax1) : 0;
     const tax2 = this.selectedTaxes.tax2 == true ? Number(data.tax2) : 0;
     const tax3 = this.selectedTaxes.tax3 == true ? Number(data.tax3) : 0;
 
     //total = data.mode=='Strip'?qty * price:qty*price/data.sheetSize;
     total = tUnits * unitPrice;
     
     const disAmount = (total / 100) * discount;
 
     const afterDis = total - disAmount;
     // const afterTax = (afterDis / 100) * (tax1 + tax2 + tax3);
     const afterTax = (afterDis);
     console.log("get sub total " + type + ' price : ' + price + ' total : ' + total + ' disAmount ' + disAmount);
     console.log('after dis ' + afterDis);
     console.log('after tax ' + afterTax);
     //sam change because the price was adding up GST even when the mrp is inclusive of gST
     //const netTotal =type=='GST'?(afterDis):(afterDis+afterTax);
     netTotal= afterDis;
 
     console.log(' net total in sale ' + netTotal);
     data.subTotal = Number(this.fixDigits(netTotal));
 
     //TOTAL UNITS
     if (data.mode == "Pack") {
       // data.totalUnit = (data.packSize * data.unit) + data.freeUnit;
       data.totalUnit=tUnits;
 
     } else if (data.mode == "Strip") {
       // data.totalUnit = (data.sheetSize * data.unit) + data.freeUnit;
       data.totalUnit=tUnits;
     } else {
       data.totalUnit = tUnits;
     }
   }
   
    return this.fixDigits(netTotal);

  }
     
  return 0;
  }

  get totalGross() {
    let total = 0;
    let taxrate=0;
    let price = 0;
    let pbt = 0;
    this.savedItemList.forEach((e) => {
      // price = e.sellingPrice * (e.leaf+e.unit/e.sheetSize);
      // price = price - price * e.itemDisc / 100;
      // taxrate = 2 * e.tax1;
      // pbt = (price * 100) / (100 + taxrate);
      // //pbt=price*taxrate/100;
      // console.log("PAD "+price);
      // console.log("tax rate in total gross " + taxrate);
      // console.log("PBT "+pbt);
      // //total = total + (price*100)/(100+taxrate);
      // //total += price -(price*2*e.tax1/100);
      // //total = (total *100)/(taxrate+100)
      // total += pbt;
      if(e.stockID!=0){
        total = total +(e.subTotal*100)/(100+2*e.tax1);

      }
    });
    console.log("***** TOTAL GROSS " + total);
    return total;
  }

  get totalCostInventory() {
    let total = 0;
    this.savedItemList.forEach((e) => {
      total = total + e.purchasePrice * e.unit;
    });

    return total;
  }

  get totalTax1() {
    let total = 0;
    let btSellingPrice = 0;
    let price = 0;
    this.savedItemList.forEach((e) => {
      if (e.stockID!=0) {
        const tax = this.selectedTaxes.tax1 == true ? Number(e.tax1) : 0;
  
        // taxrate = e.tax1;
        console.log("e selling price "+e.sellingPrice);
  
        //console.log("before tax selling price " + btSellingPrice);
        //price = btSellingPrice * (e.leaf+e.unit);
        price = e.sellingPrice * (e.leaf+e.unit/e.sheetSize);
        const pbt = (price * 100) / (100 + 2*tax);
  
        const discount = (pbt / 100) * e.itemDisc;
  
        console.log("calculating tax : selling price " + e.sellingPrice + "...pbt:" + pbt + " total price.." + price + " discount amount " + discount);
  
        //total += e.productType=='GST'?0:total + ((price - afterDisc) / 100) * tax;
        //total += ((price - afterDisc) / 100) * tax;
        total += ((pbt - discount) * tax/100);
        console.log("TOTAL AFTER TAX1 " + total +'   '+((price - discount))*.06);
      }

    });

    return Number(total.toFixed(2));
  }

  get totalTax2() {
    let total = 0;
    let btSellingPrice = 0;
    let price = 0;
    this.savedItemList.forEach((e) => {
      if (e.stockID!=0) {
        const tax = this.selectedTaxes.tax1 == true ? Number(e.tax1) : 0;
  
        // taxrate = e.tax1;
        console.log("e selling price "+e.sellingPrice);
  
        //console.log("before tax selling price " + btSellingPrice);
        //price = btSellingPrice * (e.leaf+e.unit);
        price = e.sellingPrice * (e.leaf+e.unit/e.sheetSize);
        const pbt = (price * 100) / (100 + 2*tax);
  
        const discount = (pbt / 100) * e.itemDisc;
  
        console.log("calculating tax : selling price " + e.sellingPrice + "...pbt:" + pbt + " total price.." + price + " discount amount " + discount);
  
        //total += e.productType=='GST'?0:total + ((price - afterDisc) / 100) * tax;
        //total += ((price - afterDisc) / 100) * tax;
        total += ((pbt - discount) * tax/100);
        console.log("TOTAL AFTER TAX2 " + total +'   '+((price - discount))*.06);
      }

    });

    return Number(total.toFixed(2));
  }

  get totalTax3() {
    let total = 0;
    this.savedItemList.forEach((e) => {
      const tax = this.selectedTaxes.tax3 == true ? Number(e.tax3) : 0;
      const price = e.sellingPrice * e.unit;
      const afterDisc = (price / 100) * e.itemDisc;
      total += e.productType == 'GST' ? 0 : total + ((price - afterDisc) / 100) * tax;
    });

    return Number(total.toFixed(2));
  }

  get totalDiscAmount() {
    let total = 0;
    this.savedItemList.forEach((e) => {
      const price = e.sellingPrice * e.unit;
      total = total + (price / 100) * e.itemDisc;
    });
    //alert('getdiscount total'+total);


    return total;
  }

  get netTotal() {
    //alert(" net total ");
    let adjust = 0;

    console.log("bill adjustment amount " + this.billAdjustment);
    if (Number(this.billAdjustment < 0)) {
      console.log("discount");
      return Number(
        Math.round(
          this.totalGross - this.billDiscount + Number(this.billAdjustment)
          + this.totalTax1 +
          this.totalTax2 +
          this.totalTax3
        )
      );
    } else if (Number(this.billAdjustment) > 0) {
      console.log("do addition ");
      return Number(
        Math.round(
          this.totalGross - this.billDiscount + Number(this.billAdjustment)
          + this.totalTax1 +
          this.totalTax2 +
          this.totalTax3
        )
      );
    }
    return Number(
      Math.round(
        this.totalGross - this.billDiscount - this.billAdjustment
        + this.totalTax1 +
        this.totalTax2 +
        this.totalTax3
      )
    );

  }

  //sam
  calculateDiscount(disc) {
    //alert(this.netTotal+"...bill disc "+disc);
    //this.totalDiscAmount=this.netTotal- this.netTotal*disc/100;
    //this.netTotal = this.netTotal - this.netTotal*disc/100;
    // alert(this.totalDiscAmount);
    //alert('discounted total '+this.netTotal*disc/100);
    //  this.savedItemList.forEach((e) => {
    //     // const tax = this.selectedTaxes.tax1 == true ? Number(e.tax1) : 0;
    //     // const price = e.sellingPrice * e.unit;
    //     // const afterDisc = (price / 100) * e.itemDisc;
    //     // total = total + ((price - afterDisc) / 100) * tax;

    //  e.itemDisc=disc;
    //   });
    // alert('total disacount '+this.totalDiscAmount.toFixed(2));
    //alert("disc % "+disc+"..adjustment "+this.billAdjustment);
    this.billDiscount = (this.netTotal * disc / 100);
    //alert(this.billDiscount);

  }


  setPricing(data) {
    // alert('set Purchase Price '+data.originalPPrice+'...MRP '+data.mrp);
    // alert('****data mode '+data.mode);
    if (data.mode == "Pack") {
      data.sellingPrice = data.originalSPrice;
      data.purchasePrice = data.originalPPrice;
    } else if (data.mode == "Strip") {
      if (data.sheetSize != 0) {
        //sam
        // alert('pack size : '+data.packSize);
        // data.sellingPrice = data.originalSPrice / data.sheetSize;
        //data.purchasePrice = data.originalPPrice / data.sheetSize;
        data.sellingPrice = data.mrp;
        data.purchasePrice = data.originalPPrice;
      }
    } else {
      //alert(data.mode+'...'+data.sheetSize);
      console.log(' ---Unit mode packsize  ' + data.packSize);
      if (data.packSize != 0) {
        //data.sellingPrice = data.originalSPrice / data.packSize;
        //data.purchasePrice = data.originalPPrice / data.packSize;
        data.sellingPrice = data.mrp / data.sheetSize;
        data.purchasePrice = data.originalPPrice;
      }
    }

    console.log(' ****SELLING PRICE IN SET PRICING :' + data.sellingPrice);
  }

  fixDigits(amt) {
    let total = 0;
    //alert(' fixdigts '+amt);
    if (amt != null) {
      total = amt.toFixed(2);
      // total = Math.round(amt);
    }
    return total;
  }

  clearAll() {
    this.state.selectedProfile = this.defaultCustomerName;
    this.state.transferStoreID = "";

    this.savedItemList = [];
    this.paymentList = [];

    this.item = {
      id: 0,
      profileID: this.defaultCustomerID,
      discount: 0,
      totalGrossAmt: 0,
      totalCost: 0,
      totalBill: 0,
      totalPaid: 0,
      totalTendered: 0,
      totalChange: 0,
      totalTax1: 0,
      totalTax2: 0,
      totalTax3: 0,
      totalTax: 0,
      description: "",
      searchReceiptNo: "",
      paymentMethod: "",
      doctorDetails: "",
      patientDetails: "",
      status: "Active",
      type: this.item.type,
    };

    this.itemScanBox = "";
  }

  // getMargin(savedItem){
  //   if(savedItem.mrp>0 && savedItem.purchasePrice>0){
  //     return ((savedItem.mrp-savedItem.purchasePrice)/savedItem.mrp*100).toFixed(2);
  //  }

  //  return 0;  


  // }

  // public getMargin(savedItem){
  //  console.log("#####margin" +savedItem.mrp+'....'+savedItem.purchasePrice+"...##"+this.getTheSubtotal(savedItem);
  //  if(savedItem.mrp>0 && savedItem.purchasePrice>0){
  //     return ((savedItem.mrp-this.getTheSubtotal(savedItem))/savedItem.mrp*100).toFixed(2);
  //  }

  //  return 0;  
  // }


  formatExpiryDate(d) {
    return moment(d).format("MMM-YYYY");
  }

  adjustQty(type, item) {

    if (type == "minus" && item.unit > 0) {
      item.unit--;
    } else if (type == "add") {
      if (
        item.unit + item.freeUnit < item.stockQty ||
        this.item.type == "RFD"
      ) {
        item.unit++;
      } else {
        this.toast.showError("Item does not have enough stock qty");
      }
    }
  }

  adjustFreeQty(type, item) {
    if (type == "minus" && item.freeUnit > 0) {
      item.freeUnit--;
    } else if (type == "add") {
      if (item.unit + item.freeUnit < item.stockQty) {
        item.freeUnit++;
      } else {
        this.toast.showError(
          "Item does not have enough stock qty to provide free units"
        );
      }
    }
  }

  clearListItem(item) {
    this.savedItemList.splice(this.savedItemList.indexOf(item), 1);
    this.toast.showSuccess("Item Deleted Successfully");
  }

  limitString(str) {
    if (str.length > 40) str = str.substring(0, 40) + "...";
    return str;
  }

  closePaymentScreen() {
    // alert('close payment');
    this.paymentDialog = false;
  }

  getProceededPayments(paymentList) {
    this.paymentList = paymentList;
    const tenderedList = this.getTotalPaid(paymentList);
    this.item.totalPaid = Number(tenderedList[0]);
    this.item.totalTendered = Number(tenderedList[1]);
    this.item.totalChange = Number(tenderedList[2]);

    const method = this.getPaymentMethod(paymentList);
    this.item.paymentMethod = method;


    this.item.discount = this.totalDiscAmount;
    this.item.totalGrossAmt = this.totalGross;
    this.item.totalCost = this.totalCostInventory;
    this.item.totalBill = this.netTotal;
    this.item.totalTax1 = this.totalTax1;
    this.item.totalTax2 = this.totalTax2;
    this.item.totalTax3 = this.totalTax3;
    this.item.totalTax = Number(
      this.totalTax1 + this.totalTax2 + this.totalTax3
    );

    this.setAccountingEntries();

    //check if there are any blank items and splice
    this.savedItemList.forEach((e, index) => {
      console.log("subtotal for each item " + e.subTotal);
      if (e.subTotal == 0) {
        this.savedItemList.splice(index, 1);
      }
    });

    console.log("After splicing the blank items " + JSON.stringify(this.savedItemList));
    console.log("Counter entry "+JSON.stringify( this.counterEntry));
    //alert(JSON.stringify(this.savedItemList));
    //alert('receipt no '+this.item.searchReceiptNo);
    this.posService
      .saveItem(this.item, this.paymentList, this.state, this.savedItemList, this.counterEntry)
      .then((res) => {
        if (res.alert == "info") {
        alert("res data "+res.rno);
         document.getElementById("billNo").textContent=res.rno;
         var a = window.open('', '', 'height=500, width=500');
          a?.document.write(document.getElementById('invoiceArea')?.innerHTML);
          a?.print();
          this.clearAll();
          this.openNewRow();

        }

        this.toast.handleResponse(res);
      });

    this.paymentDialog = false;
    this.submitted = false;


  }

  openPaymentMethod(isFormValid) {
    //alert('before open payment total' +this.netTotal+" total bill "+this.item.totalBill);

    this.submitted = true;
    if (isFormValid = true) {
      this.paymentDialog = true;
      this.store.dispatch(
        ActionTypes.TOTAL_BILL,
        Number(this.fixDigits(this.netTotal))
        // Number(Math.round(this.netTotal))

        // this.netTotal - this.netTotal*item.discount/100;

      );
    }
  }

  get totalPaidCash() {
    let total = 0;

    this.paymentList.forEach(e => {
      if (e.paymentType == 'Cash') {
        total = total + e.transTotalAmount;
      }
    });

    return total;
  }

  get totalPaidBank() {
    let total = 0;

    this.paymentList.forEach(e => {
      if (e.paymentType != 'Cash') {
        total = total + e.transTotalAmount;
      }
    });

    return total;
  }

  getTotalPaid(paymentList) {
    let totalPaid = 0;
    let totalTendered = 0;
    let totalChange = 0;

    paymentList.forEach((e) => {
      if (e.paymentType != "Tip") {
        totalPaid = totalPaid + Number(e.transTotalAmount);
        totalTendered = totalTendered + Number(e.tendered);
        totalChange = totalChange + Number(e.change);
      }
    });

    return [totalPaid, totalTendered, totalChange];
  }

  get totalBalance() {
    return this.netTotal - this.item.totalPaid;
  }

  getPaymentMethod(paymentList) {
    let method = "";

    if (paymentList.length == 0) {
      method = "Pay Later";
    } else if (paymentList.length == 1) {
      method = paymentList[0].paymentType;
    } else if (paymentList.length > 1) {
      method = "Split";
    }

    return method;
  }

  clearFreeUnit() {
    this.toast.showSuccess("Mode Changed Successfully");

    if (this.item.type != "TRN") {
      this.savedItemList.forEach((e) => {
        e.freeUnit = 0;
      });
    }

    if (this.item.type == "RFD") {
      this.refundReceiptDialog = true;
    }

    if (this.item.type == "MDF") {
      this.refundReceiptDialog = true;
    }

    this.submitted = false;
    this.clearAll();
  }

  sumUnitTotalQty(data) {
    let tUnit = 0;

    this.savedItemList.forEach((e) => {
     if (e.stockID!=0) {
      //  console.log('strip size ' + e.sheetSize);
      //  console.log('available quantity in strips ' + e.packSize);
      //  console.log('Unit ' + e.unit);
      //  console.log('Leaf :'+e.leaf);
      //  console.log('mode ' + e.mode);
       if (data.stockID == e.stockID) {
         if (e.mode == "Pack") {
          tUnit = tUnit + (e.packSize * e.unit) + e.freeUnit;
         } else if (e.mode == "Strip") {
           // totalUnits = totalUnits + (e.sheetSize * e.unit) + e.freeUnit;
           tUnit = tUnit + e.leaf*e.sheetSize+e.unit;
 
         } else {
          tUnit = tUnit + (e.freeUnit + e.unit);
         }
       }
     }
    });
    console.log("Total units to be purchased " + tUnit);
    // console.log("-----------------------");
    return Number(tUnit);
  }

  fetchReceiptNo() {
    alert('searching for receipt '+this.item.searchReceiptNo)
    this.posService.getReceiptItems(this.item.searchReceiptNo).then((data) => {
      if (data.receipt != null) {
        this.state.selectedProfile = data.receipt.profile_name.accountName;
        this.item.profileID = data.receipt.profile_name.id;
        this.item.patientDetails =
          data.receipt.patient_details == null
            ? ""
            : data.receipt.patient_details;
        this.item.description =
          data.receipt.description == null ? "" : data.receipt.description;
        this.item.doctorDetails =
          data.receipt.doctor_details == null
            ? ""
            : data.receipt.doctor_details;

        if (data.receipt.total_tax1 > 0) {
          this.selectedTaxes.tax1 = true;
        }

        if (data.receipt.total_tax2 > 0) {
          this.selectedTaxes.tax2 = true;
        }

        if (data.receipt.total_tax3 > 0) {
          this.selectedTaxes.tax3 = true;
        }
      }

      if (data.receiptItems != null) {
        
        data.receiptItems.forEach((e) => {
          this.savedItemList.push({
            mode: e.mode,
            stockID: Number(e.stock_id),
            productName: e.item_name,
            generic: e.generic_name,
            itemDescription: e.item_description,
            leaf: Math.floor((e.total_unit/e.sheet_size)),
            unit: Number(e.unit),
            totalUnit: Number(e.total_unit),
            stockQty: Number(e.stock_detail.qty),
            freeUnit: Number(e.free_unit),
            supplierBonus: Number(e.supplier_bonus),
            batchNo: e.batch_no,
            packSize: Number(e.pack_size),
            sheetSize: Number(e.sheet_size),
            purchasePrice: Number(e.purchase_price),
            originalPPrice: Number(e.purchase_price),
            originalSPrice: Number(e.stock_detail.sale_price),
            sellingPrice: Number(e.selling_price),
            mrp: Number(e.mrp),
            brandName: e.brand_name,
            sectorName: e.sector_name,
            categoryName: e.category_name,
            productType: e.product_type,
            expiryDate: e.expiry_date,
            itemDisc: Number(e.item_disc),
            tax1: Number(e.tax_1),
            tax2: Number(e.tax_2),
            tax3: Number(e.tax_3),
            subTotal: Number(e.sub_total),
            
          });
        });

        this.refundReceiptDialog = false;
      }
    });
  }

  //OPEN DIALOG TO ADD NEW ITEM
  openProfileDialog() {
    this.dialogTitle = "Add New Profile";
    this.profileStatus = true;
    this.statusType = "New";
  }

  updateProfilerStatus(res) {
    this.profileStatus = false;
    if (res[0] == "load") {
      this.state.selectedProfile = res[1].account_title;
      this.item.profileID = res[1].id;
    }
  }

  redirectHome() {
    this.screenState.fullscreen = false;
    router.replace({ path: "/store/dashboard", params: {} });
  }

  setAccountingEntries() {

    this.counterEntry = [];

    if (this.item.type == "INE") {
      if (this.totalBalance == 0) {

        if (this.totalPaidCash > 0) {
          this.counterEntry.push({
            accountID: 2,
            accountHead: 'Cash in hand',
            amount: this.totalPaidCash,
            type: "Debit",
          });
        }

        if (this.totalPaidBank > 0) {
          this.counterEntry.push({
            accountID: 8,
            accountHead: 'Cash at bank',
            amount: this.totalPaidBank,
            type: "Debit",
          });
        }

      } else if (this.totalBalance == this.netTotal) {
        this.counterEntry.push({
          accountID: 4,
          accountHead: "Accounts receivable",
          amount: this.netTotal,
          type: "Debit",
        });
      } else if (this.totalBalance > 0 && this.item.totalPaid > 0) {
        if (this.totalPaidCash > 0) {
          this.counterEntry.push({
            accountID: 2,
            accountHead: 'Cash in hand',
            amount: this.totalPaidCash,
            type: "Debit",
          });
        }

        if (this.totalPaidBank > 0) {
          this.counterEntry.push({
            accountID: 8,
            accountHead: 'Cash at bank',
            amount: this.totalPaidBank,
            type: "Debit",
          });
        }

        this.counterEntry.push({
          accountID: 4,
          accountHead: "Accounts receivable",
          amount: this.totalBalance,
          type: "Debit",
        });
      }

      this.counterEntry.push({
        accountID: 56,
        accountHead: 'Cost of Goods',
        amount: this.totalCostInventory,
        type: "Debit",
      });


      this.counterEntry.push({
        accountID: 48,
        accountHead: 'Sales Revenue',
        amount: this.totalGross - this.totalDiscAmount,
        type: "Credit",
      });

      this.counterEntry.push({
        accountID: 3,
        accountHead: 'Inventory',
        amount: this.totalCostInventory,
        type: "Credit",
      });

      //ADDING TAXES
      if (this.totalTax1 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[0].accountID,
          accountHead: this.taxNames[0].accountHead,
          amount: this.totalTax1,
          type: "Credit",
        });
      }

      if (this.totalTax2 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[1].accountID,
          accountHead: this.taxNames[1].accountHead,
          amount: this.totalTax2,
          type: "Credit",
        });
      }

      if (this.totalTax3 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[2].accountID,
          accountHead: this.taxNames[2].accountHead,
          amount: this.totalTax3,
          type: "Credit",
        });
      }
    } else if (this.item.type == "RFD") {

      this.counterEntry.push({
        accountID: 48,
        accountHead: 'Sales Revenue',
        amount: this.totalGross - this.totalDiscAmount,
        type: "Debit",
      });

      this.counterEntry.push({
        accountID: 3,
        accountHead: 'Inventory',
        amount: this.totalCostInventory,
        type: "Debit",
      });

      //ADDING TAXES
      if (this.totalTax1 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[0].accountID,
          accountHead: this.taxNames[0].accountHead,
          amount: this.totalTax1,
          type: "Debit",
        });
      }

      if (this.totalTax2 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[1].accountID,
          accountHead: this.taxNames[1].accountHead,
          amount: this.totalTax2,
          type: "Debit",
        });
      }

      if (this.totalTax3 != 0) {
        this.counterEntry.push({
          accountID: this.taxNames[2].accountID,
          accountHead: this.taxNames[2].accountHead,
          amount: this.totalTax3,
          type: "Debit",
        });
      }

      if (this.totalBalance == 0) {

        if (this.totalPaidCash > 0) {
          this.counterEntry.push({
            accountID: 2,
            accountHead: 'Cash in hand',
            amount: this.totalPaidCash,
            type: "Credit",
          });
        }

        if (this.totalPaidBank > 0) {
          this.counterEntry.push({
            accountID: 8,
            accountHead: 'Cash at bank',
            amount: this.totalPaidBank,
            type: "Credit",
          });
        }

      } else if (this.totalBalance == this.netTotal) {
        this.counterEntry.push({
          accountID: 5,
          accountHead: "Accounts payable",
          amount: this.netTotal,
          type: "Credit",
        });
      } else if (this.totalBalance > 0 && this.item.totalPaid > 0) {
        if (this.totalPaidCash > 0) {
          this.counterEntry.push({
            accountID: 2,
            accountHead: 'Cash in hand',
            amount: this.totalPaidCash,
            type: "Credit",
          });
        }

        if (this.totalPaidBank > 0) {
          this.counterEntry.push({
            accountID: 8,
            accountHead: 'Cash at bank',
            amount: this.totalPaidBank,
            type: "Credit",
          });
        }

        this.counterEntry.push({
          accountID: 5,
          accountHead: "Accounts payable",
          amount: this.totalBalance,
          type: "Credit",
        });
      }

      this.counterEntry.push({
        accountID: 56,
        accountHead: 'Cost of Goods',
        amount: this.totalCostInventory,
        type: "Credit",
      });
    }
    else {
      //TRANSFER ENTRY NO NEED
    }

  }

  get currency() {
    return this.store.getters.getCurrency;
  }
}
</script>

<style>
.b-style {
  border-radius: 0px;
}

.item-detail-row {
  background-color: #0b932a;
  border-bottom: 2px solid #ccc;
  color: #fff;
}

.item-detail-row td {
  font-size: 12px;
  padding: 1px;
}

.apply-style {
  margin-top: 2px;
  padding: 5px;
  width: 100%;
  border: none;
}

.pos-heading {
  background-color: #11467e;
  color: #fff;
}

.pos-heading th {
  padding: 0;
  padding-left: 3px;
  text-transform: uppercase;
}

.remove-item {
  color: #c00;
}

.remove-item:hover {
  vertical-align: center;
  cursor: pointer;
}

.table-row td {
  padding: 3px;
}

.pos-table {
  height: 83vh;
  max-height: 83vh;
  overflow-y: scroll;
  background-color: #fff;
}

.set-bottom-amt {
  text-align: center;
  text-transform: uppercase;
  font-size: 14px;
  background-color: #11467e;
  border-right: 1px dotted #ccc;
  color: #fff;
}
.p-dialog-content ul li{
  padding: 5px;
  list-style-type: none;
  
}

.p-dialog-content ul li:hover{
 cursor: pointer;
  
}
.p-dialog-content ul li:nth-child(odd){
  background: #057cfe;
  color: white;
}


/* .p-autocomplete-item ul li:hover ul  {
  display: block;
  position: relative;
  top: 0;
  left: 2%;
 
}
.p-autocomplete-item ul li ul  {
  display: none;
} */

/* .p-autocomplete-panel .p-autocomplete-items .p-autocomplete-item-group:hover {

  display: block;
  background: red;
}
.p-autocomplete-panel .p-autocomplete-items .p-autocomplete-item{
  display: none;
  background:#0b932a;
  width: 500px;
  left: 2%;
  color: white;
}
.p-autocomplete-item ul li ul li:nth-child(odd){
  background:#11467e;
} */

/* Requested Medicine Styles */
.requested-item {
  background: linear-gradient(to right, #fff3cd 0%, #ffffff 100%) !important;
  border-left: 4px solid #ff9800 !important;
  padding: 8px !important;
}

.requested-badge {
  display: inline-block;
  background-color: #ff9800;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: bold;
  margin-right: 8px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.requested-customer-info {
  color: #666;
  font-size: 11px;
  margin-top: 4px;
  padding-left: 4px;
}

.requested-customer-info small {
  color: #0b932a;
  font-weight: 600;
}

/* Optional: responsive for mobile */
.p-dialog .p-dialog-header .p-dialog-header-icon {
    width: 2rem;
    height: 2rem;
    color: #6c757d;
    border: 0 none;
    background: transparent;
    border-radius: 50%;
    transition: box-shadow 0.15s;
    display: none!important;
    margin-right: 0.5rem;
}
</style>