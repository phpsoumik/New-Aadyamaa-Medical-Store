<template>
 
  <section>
     <div style="height: 90vh;">
        <div style="height: 0.5vh; background-color:#ccc;">
          <ProgressBar
          v-if="progressBar"
          mode="indeterminate"
          style="height: 0.2em"
        />
        </div>
        <Toolbar style="height: 7.5vh">
        <template #start>
          <h5 class="p-mt-2">
            <b>
              <i style="font-size: 20px" class="pi pi-globe"></i>
              {{ storeName }}
            </b>
          </h5>
        </template>
        <template #end>
          <div class="p-mx-1">
            <Dropdown
              style="width: 15rem"
              v-model="item.type"
              :options="modeList"
              optionLabel="label"
              optionValue="value"
              @change="clearFreeUnit()"
            />
          </div>
<!--sam-->
          <div class="p-mx-1">
            <Button
              icon="pi pi-plus"
              label="Add Row"
              class="p-button-success"
              @click="openNewRow"
            />
          </div>

          <!-- <div class="p-mx-1">
            <Button
              icon="pi pi-plus"
              label="Add Item"
              class="p-button-success"
              @click="openItemDialog"
            />
          </div> -->
            
          <div class="p-mx-1">
            <Button
              icon="pi pi-plus"
              label="Upload CSV"
              class="p-button-success"
              @click="openFileUploader"
            />
          </div>
<!--end sam-->              

          <div class="p-mx-1">
            <Button
              icon="pi pi-plus"
              label="New Item"
              class="p-button-success"
              @click="openProfileDialog"
            />
          </div>
          <!-- <div class="p-mx-1">
            <Button
              icon="pi pi-times"
              label="Clear Screen"
              class="p-button-danger"
              @click="clearAll"
            />
          </div> -->
        </template>
      </Toolbar>
      <div class="p-col-12" style="height: 8vh;">
         <div class="p-grid">
            
            <div class="p-col-3">
              <div class="p-fluid">
                <AutoComplete
                  :delay="1000"
                  :minLength="3"
                  @item-select="saveProfile($event)"
                  scrollHeight="500px"
                  v-model="v$.selectedProfile.$model"
                  :suggestions="profilerList"
                  placeholder="Search Supplier"
                  @complete="searchProfiler($event)"
                  :dropdown="false"
                  autoFocus
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
                <span v-if="v$.selectedProfile.$error && submitted">
                  <span
                    id="p-error"
                    v-for="(error, index) of v$.selectedProfile.$errors"
                    :key="index"
                  >
                    <small class="p-error">{{ error.$message }}</small>
                  </span>
                </span>
                <small
                  v-else-if="
                    (v$.selectedProfile.$invalid && submitted) ||
                    v$.selectedProfile.$pending.$response
                  "
                  class="p-error"
                  >{{
                    v$.selectedProfile.required.$message.replace(
                      "Value",
                      "Profile"
                    )
                  }}</small
                >
              </div> <!-- end of profile-->
              

            </div>
            <div class="col-3 p-inputgroup" style="margin:5px;">
                  <InputText placeholder="Bill No" style="margin: 10px;width: 14rem;" v-model="item.billNo" id="bNo"/>
                  <InputText placeholder="Bill Date" style="margin: 10px;width: 14rem;" v-model="item.billDate" />
                  <label style="margin-top:20px;">BILL TYPE </label>
                  <select @change="setGSTOption()" style="margin: 10px;width: 25rem; height:35px" v-model="item.pType" >
                        <option value="100">With GST</option>
                        <option value="101">Without GST</option>
                  </select>
              </div>
              
             
        </div>
      </div>
      <div class="p-col-12 pos-table" style="height: 64vh; background-color: #f9f9f9">
        <table class="table table-stiped table-bordered p-m-0">
            <thead style="position: sticky; top: 0; z-index: 1; ">
              <tr class="pos-heading">
                <th style="width: 2rem">BarCode</th>
                <th style="width: 20rem">Name</th>
                <th style="width: 4rem">Batch</th>
                <th style="width: 4rem">Strip Size</th>
                <th>Qnty</th>
                <th style="width: 4rem">Free Pack</th>
                <th style="width: 4rem">HSN</th>
                <th style="width:8rem">P. Price</th>
                <th>P.Disc (%)</th>
                <th>S. Disc (%)</th>
                <th style="width: 8rem;">MRP</th>
                <th style="width:2rem">Margin (%)</th>
                <th style="width: 2rem">Expiry Date</th>
                <th v-if="taxNames[0].show">{{taxNames[0].taxName}} %</th>
                <th v-if="taxNames[1].show">{{taxNames[1].taxName}} %</th>
                <th v-if="taxNames[2].show">{{taxNames[2].taxName}} %</th>
                <th>Subtotal ({{currency}})</th>
                <th>Cus Disc %</th>

                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <template
                v-for="(savedItem, index) in savedItemList.slice()"
                :key="savedItem">
                <tr class="table-row">
                  <td v-if="savedItem.preturn==0 || this.viewInvoice==true">
                    <InputText
                        :useGrouping="false"
                        style="width: 5rem; height: 30px"
                        :min="1"
                        v-model="savedItem.barcode"
                        class="p-p-0"
                      />
                  </td>
                  <td v-else="savedItem.preturn==1">
                    <input type="checkbox">
                  </td>
                  <td>
                    <div class="p-inputgroup">
                     
                      <!-- this is soumik code - fixed product selection and dropdown close -->
                      <AutoComplete
                          :delay="1000"
                          :minLength="3"
                          @key=index
                          @item-select="saveItem($event, index)"
                          scrollHeight="500px"
                          v-model="savedItem.productName"
                          :suggestions="itemList"
                          field="product_name"
                          placeholder=" SCAN BARCODE OR SEARCH ITEMS"
                          @complete="searchItem($event)"
                          :dropdown="false"
                          :completeOnFocus="false"
                          :forceSelection="true"
                          autoFocus

                       >
                       <template #item="slotProps">
                    <div :style="slotProps.item.is_requested ? 'background-color: #fff3cd; padding: 5px; border-left: 3px solid #ffc107;' : ''">
                      <span class="p-mr-1">
                        NAME :
                        <b class="pull-right">
                          {{ slotProps.item.product_name.toUpperCase() }}
                          <span v-if="slotProps.item.is_requested" style="color: #ff6b6b; font-size: 11px; margin-left: 5px;">
                            [REQUESTED ITEM]
                          </span>
                        </b>
                      </span>
                      <span class="p-mx-1" v-if="!slotProps.item.is_requested">
                        EXPIRY DATE :
                        <b class="pull-right">
                          {{ formatExpiryDate(slotProps.item.expiry_date) }}
                        </b>
                      </span>
                    </div>
                    <div v-if="!slotProps.item.is_requested">
                      <small>
                        BATCH NO :
                        <b class="pull-right">
                          {{ slotProps.item.batch_no }}
                        </b>
                      </small>
                      <small>
                        Total Units :
                        <b class="pull-right">
                          {{ slotProps.item.qty }}
                        </b>
                      </small>
                      <small>
                        MRP :
                        <b class="pull-right">
                         {{currency}} {{ slotProps.item.mrp }}
                        </b>
                      </small>
                    </div>
                    <div v-else style="padding: 3px 0;">
                      <small style="color: #856404;">
                        <i class="pi pi-info-circle"></i> This is a customer requested medicine
                      </small>
                    </div>
                  </template>
                       </AutoComplete>
                    </div>
                  </td>
                  <td >
                    <div class="p-inputgroup">
                      <InputText
                        style="height: 30px;width: 4rem;"
                        v-model="savedItem.batchNo"
                        class="p-p-1"
                        :disabled="!validItem(index)"
                        
                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :min="1"
                        v-model="savedItem.sheetSize"
                        class="p-p-0"
                        :disabled="!validItem(index)"
                        

                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :min="1"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.packSize"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :min="0"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.freeUnit"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 5rem; height: 30px"
                        :min="1"
                        v-model="savedItem.hsn"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        style="width: 2rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="5"
                        v-model="savedItem.purchasePrice"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.specialDisc"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      /> 
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.cusDisc"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        style="width: 1rem; height: 30px"
                        :min="1"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.mrp"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td>
                    <!-- <div class="p-inputgroup"> -->
                      <b> {{calcMargin(savedItem)}} </b>

                    <!-- </div> -->
                  </td>
                 
                  <td>
                    <div class="p-inputgroup">
                       <InputText
                          id="expiryDate"
                          v-model="savedItem.expiryDate"
                          style="width: 3rem; height: 30px"
                          selectionMode="single"
                          dateFormat="dd-mm-yy"
                          class="p-p-0"
                          :disabled="!validItem(index)"

                          
                        />
                    </div>
                  </td>
                  <td v-if="taxNames[0].show">
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        :maxFractionDigits="2"
                        style="width: 3rem; height: 30px"
                        v-model="savedItem.tax1"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td v-if="taxNames[1].show">
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        :maxFractionDigits="2"
                        style="width: 3rem; height: 30px"
                        v-model="savedItem.tax2"
                        @focus="setOtherTax(savedItem)"

                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td v-if="taxNames[2].show">
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :maxFractionDigits="2"
                        v-model="savedItem.tax3"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td class="p-text-center">
                    <b> {{getTheSubtotal(savedItem)}} </b>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 1rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="savedItem.itemDisc"
                        class="p-p-0"
                        :disabled="!validItem(index)"

                      />
                    </div>
                  </td>
                  <td class="p-text-center">
                    <Button
                      icon="pi pi-minus"
                      class="p-button-danger p-p-1"
                      style="font-size: 1rem;"
                      @click="clearListItem(savedItem)"
                    />
                  </td> <!--upto this depends on stock id-->
                
                </tr>

                <!-- upto this-->
                <tr class="item-detail-row" v-if="savedItem.productID != 0">
                  <td  :colspan="countTaxesLen">
                    <span class="p-mr-1">
                      PRODUCT NAME:
                      <span style="color: #fff; background-color: #c00">
                        {{ limitString(savedItem.productName) }}
                      </span></span
                    >
                    <span class="p-mr-1">
                      GENERIC:
                      <span style="color: #fff; background-color: #c00">
                        {{ limitString(savedItem.generic) }}
                      </span></span
                    >
                    <span class="p-mx-1">
                      STRIP SIZE : {{ savedItem.sheetSize }}
                    </span>
                    <span class="p-mx-1">
                      QUANTITY : {{ savedItem.packSize*savedItem.sheetSize }}
                    </span>
                    <!-- <span class="p-mx-1">
                      TOTAL UNITS : {{ fixDigits(savedItem.totalUnit) }}
                    </span>
                    <span class="p-mx-1">
                       PRICE(Incl GST) : {{currency}} {{ fixDigits(savedItem.mrp) }}
                    </span> -->
                  </td>
                </tr>
              </template>
            </tbody>

            <!--return item table-->
          </table>

          <!-- <thead style="position: sticky; top: 0; z-index: 1; ">
              <tr class="pos-heading">
                <th style="width: 2rem"></th>
                <th style="width: 20rem">Name</th>
                <th style="width: 4rem">Batch</th>
                <th style="width: 4rem">Strip Size</th>
                <th>Qnty</th>
                <th style="width: 4rem">Free Pack</th>
                <th style="width: 4rem">HSN</th>
                <th style="width:8rem">P. Price</th>
                <th>P.Disc (%)</th>
                <th>S. Disc (%)</th>
                <th style="width: 8rem;">MRP</th>
                <th style="width:2rem">Margin (%)</th>
                <th style="width: 2rem">Expiry Date</th>
                <th v-if="taxNames[0].show">{{taxNames[0].taxName}} %</th>
                <th v-if="taxNames[1].show">{{taxNames[1].taxName}} %</th>
                <th v-if="taxNames[2].show">{{taxNames[2].taxName}} %</th>
                <th>Subtotal ({{currency}})</th>
                <th>Cus Disc %</th>

                <th>Delete</th>
              </tr>
            </thead> -->
          <h4 style="padding-top: 20px;">RETURN ITEMS</h4>
          <table class="table table-stiped table-bordered p-m-0" >
            <thead style="position: sticky; top: 0; z-index: 1; ">
              <tr class="pos-heading" style="background-color: RED;">
                <th style="width: 2rem"></th>
                <th style="width: 20rem">Name</th>
                <th style="width: 4rem">Batch</th>
                <th style="width: 4rem">Strip Size</th>
                <th>Qnty</th>
                <th style="width: 4rem">Free Pack</th>
                <th style="width: 4rem">HSN</th>
                <th style="width:8rem">P. Price</th>
                <th>P.Disc (%)</th>
                <th>S. Disc (%)</th>
                <th style="width: 8rem;">MRP</th>
                <th style="width:2rem">Margin (%)</th>
                <th style="width: 2rem">Expiry Date</th>
                <th v-if="taxNames[0].show">{{taxNames[0].taxName}} %</th>
                <th v-if="taxNames[1].show">{{taxNames[1].taxName}} %</th>
                <th v-if="taxNames[2].show">{{taxNames[2].taxName}} %</th>
                <th>Subtotal ({{currency}})</th>
                <th>Cus Disc %</th>

                <th>Delete</th>
              </tr>
            </thead>
          <tbody v-for="(returnItem, index) in returnList.slice()"
                :key="returnItem">
            <tr class="table-row">
                
                  <td style="width: 1.5rem">
                    <input type="checkbox" name ="pl" :id="returnItem.stockID" >
                  
                      
                  </td>
                  <td>
                    <div> 
                      <InputText
                        style="height: 30px;width: 10rem;"
                        v-model="returnItem.productName"
                        class="p-p-1"
                        readonly
                      />
                    </div>
                    </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        style="height: 30px;width: 4rem;"
                        v-model="returnItem.batchNo"
                        class="p-p-1"
                        readonly
                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :min="1"
                        v-model="returnItem.sheetSize"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :min="1"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.packSize"
                        class="p-p-0"

                      />
                    </div>
                  </td>
                  <td>
                    <!-- <div class="p-inputgroup">
                      <InputNumber
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :min="0"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.freeUnit"
                        class="p-p-0"
                      />
                    </div> -->
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <!-- <InputNumber
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :min="1"
                        v-model="returnItem.barcode"
                        class="p-p-0"
                      /> -->
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        style="width: 6rem; height: 30px"
                        :min="1"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.purchasePrice"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.specialDisc"
                        :disabled="false"
                        class="p-p-0"
                        readonly
                      /> 
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        style="width: 2rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.cusDisc"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        style="width: 4rem; height: 30px"
                        :min="800.62"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.mrp"
                        class="p-p-0"
                        readonly
                      />
                      <!-- v-model="returnItem.purchasePrice" -->

                    </div>
                  </td>
                  <td>
                    <!-- <div class="p-inputgroup"> -->
                      <!-- <b> {{calcMargin(returnItem)}} </b> -->

                    <!-- </div> -->
                  </td>
                 
                  <td>
                    <div class="p-inputgroup">
                       <InputText
                          id="expiryDate"
                          v-model="returnItem.expiryDate"
                          style="width: 3rem; height: 30px"
                          selectionMode="single"
                          dateFormat="dd-mm-yy"
                          class="p-p-0"
                          readonly
                          
                        />
                    </div>
                  </td>
                  <td v-if="taxNames[0].show">
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        :maxFractionDigits="2"
                        style="width: 1rem; height: 30px"
                        v-model="returnItem.tax1"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td v-if="taxNames[1].show">
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        :maxFractionDigits="2"
                        style="width: 1rem; height: 30px"
                        v-model="returnItem.tax2"
                        @focus="setOtherTax(returnItem)"

                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td v-if="taxNames[2].show">
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        style="width: 3rem; height: 30px"
                        :maxFractionDigits="2"
                        v-model="returnItem.tax3"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td class="p-text-center">
                    <b> {{getTheSubtotal(returnItem)}} </b>

                  </td>
                  <td>
                    <div class="p-inputgroup">
                      <InputText
                        :useGrouping="false"
                        style="width: 1rem; height: 30px"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        v-model="returnItem.itemDisc"
                        class="p-p-0"
                        readonly
                      />
                    </div>
                  </td>
                  <td class="p-text-center">
                    <Button
                      icon="pi pi-minus"
                      class="p-button-danger p-p-1"
                      style="font-size: 1rem;"
                      @click="clearReturnItem(returnItem)"
                    />
                  </td>
                </tr>
              </tbody>

          </table>
          <Button style="align:right" v-if="returnList.length !=0" @click="adjustReturnPricing()">ADJUST</Button>

      </div>
      <div class="p-col-12" style="height: 10vh; background-color:#eee">
       
        <div class="col-8 p-inputgroup" style="margin:5px;">
          <label style="margin-top: 15px; margin-right: 15px;">Adjustment Description</label>
          <InputText placeholder="Adjustment Description"/>
          <label style="margin-top: 15px; margin-right: 15px; margin-left: 25px;">Adjustment Amount</label>
          <InputText placeholder="adjustment Amount"  v-model=this.totalExpAdjustment id="expadjust"/>
         
        </div>
        <!-- <div class="p-grid">
          <div class="p-col-9 p-pr-0">
          </div>
          <div class="p-col-2">
              <InputText placeholder="Adjustment Description"/>
              <InputText placeholder="adjustment Amount"  v-model=this.totalExpAdjustment id="expadjust"/>

          
         
          </div>
          
         
        </div> -->
      </div>

      
     </div>  
      <div style="height: 10vh" class="p-grid p-m-0 p-p-0">
      <Button
        class="p-col p-button-success b-style"
        icon="pi pi-home"
        label="HOME"
        @click="redirectHome()"
      />
      <span class="set-bottom-amt p-col"
        >Total QTY <br />
        # {{ savedItemList.length }}</span
      >
      <span class="set-bottom-amt p-col"
        >Total Gross <br />
        {{currency}} {{ fixDigits(totalGross) }}</span
      >
      <span class="set-bottom-amt p-col"
        >Total Disc <br />
        {{currency}} {{ fixDigits(totalDiscAmount) }}</span
      >
      <span class="set-bottom-amt p-col" v-if="taxNames[0].show"
        >Total {{ taxNames[0].taxName }} <br />
        {{currency}} {{ fixDigits(totalTax1) }}</span
      >
      <span class="set-bottom-amt p-col" v-if="taxNames[1].show"
        >Total {{ taxNames[1].taxName }} <br />
        {{currency}} {{ fixDigits(totalTax2) }}</span
      >
     
      <span class="set-bottom-amt p-col"
        >Net Total <br />
        {{currency}} {{ fixDigits(netTotal) }}
      </span>
      <Button
        class="p-col p-button-warning b-style"
        icon="pi pi-arrow-right"
        label="NEXT"
        @click="openPaymentMethod(!v$.$invalid)"
        :disabled="item.profileID == 0"

      />
      <!-- @click="openPaymentMethod(!v$.$invalid)" -->
      <!-- :disabled="item.profileID == 0 || netTotal <= 0" -->

    </div>
  </section>
        <!-- //@click="openPaymentMethod(!v$.$invalid)" -->
        <!-- @click="getProceededPayments" -->

    <Dialog
    v-model:visible="receiptDailog"
    :style="{ width: '600px' }"
    header="Search Receipt"
    position="top"
  >
    <div class="confirmation-content">
      <i class="pi pi-search p-mr-3" style="font-size: 2rem" />
      <div class="p-fluid">
        <label for="search_receipt"> Enter Receipt No </label>
        <InputText
          id="search_receipt"
          autoFocus
          v-model="item.searchReceiptNo"
          placeholder="e.g PUR-02502100000000"
        />
      </div>
    </div>
    <template #footer>
      <Button
        label="No"
        icon="pi pi-times"
        class="p-button-text"
        @click="refundReceiptDialog = false"
      />
      <Button
        label="Search"
        icon="pi pi-search"
        class="p-button-success"
        :disabled="item.searchReceiptNo == ''"
        @click="fetchReceiptNo()"
      />
    </template>
  </Dialog>
  
<PaymentScreen
  :receiptDetail="{
    dialogStatus: paymentDialog,
    itemSource: item.type,
    restriction: 'No',
    dialogTilte: dialogTitle,
    customerID: this.item.profileID,
    customerName: this.state.selectedProfile,
    closeConfirmation: true,
  }"
  v-on:closePaymentScreenEvent="closePaymentScreen"
  v-on:getProceededPaymentsEvent="getProceededPayments"
/>

<!-- <ProfilerDialog
  :profilerDetail="{
    status: this.profileStatus,
    profilerID: 0,
    statusType: this.statusType,
    dialogTitle: this.dialogTitle,
    currentUserID: this.currentUserID,
  }"
  v-on:updateProfilerStatus="updateProfilerStatus"
/> -->
<ItemDialog
  :itemDetails="{
    status: this.profileStatus,
    profilerID: 0,
    statusType: this.statusType,
    dialogTitle: this.dialogTitle,
    currentUserID: this.currentUserID,
  }"
  v-on:updateProfilerStatus="updateProfilerStatus"
/>
<FileUploader
      :uploaderDetail="{
        status: uploaderStatus,
        dialogTitle: 'Upload Stock CSV file:',
        imageType: '.csv',
      }"
      v-on:updateUploaderStatus="updateUploaderStatus"
    />
</template>
<script lang="ts">
import { Options, Vue } from "vue-class-component";
import PosService from "../../service/PosService.js";
import ProfilerService from "../../service/ProfilerService.js";
import ChartService from "../../service/ChartService.js";
import useVuelidate from "@vuelidate/core";
import { required, requiredIf, helpers } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment, { relativeTimeThreshold } from "moment";
import AutoComplete from "primevue/autocomplete";
import SearchFilter from "../../components/SearchFilter.vue";
import PreviewAccountingReceipt from "../../components/PreviewAccountingReceipt.vue";
import { ref, defineComponent, toRefs, reactive } from "vue";
import PaymentScreen from "../../components/PaymentScreen.vue";
import { useStore, ActionTypes } from "../../store";
// import ProfilerDialog from "../../components/ProfilerDialog.vue";
import { ItemList,CounterEntry,PaymentListType } from "../pos_purchase/IPurchaseReceipt";
import router from "../../router";

//sam
import FileUploader from "../../components/FileUploader.vue";
import StockService from "../../service/StockService.js";
import ItemDialog from "../../components/ItemDialog.vue";


@Options({
title: 'Purchases',
components: {
  AutoComplete,
  SearchFilter,
  PreviewAccountingReceipt,
  PaymentScreen,
  // ProfilerDialog,
  FileUploader,
  ItemDialog
},
})

export default class PosPurchase extends Vue {
private modeList = [
  { label: "PURCHASE", value: "PUR" },
  { label: "PURCHASE RETURN", value: "RPU" },
  { label: "CHALLAN", value: "CHAL" },

];

private paymentDialog = false;
private itemDialog=false;
private profileStatus = false;
private statusType = "";
private storeName = "Addya Ma";
private receiptDailog = false;
private submitted = false;
private currentUserID = 0;
private itemScanBox = "";
private dialogTitle = "";
private profilerService;
private posService;
private toast;
private storeList = [];
private profilerList = [];
private itemList = [];
private store = useStore();

//sam
private uploaderStatus = false;
private stockService;
private totalInValid = 0;
private totalValid = 0;
private selectedSupplier=0;
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

private allproductTypes=[{
    
  
    option_name: "GST",
     value:101,
  },
  {
    option_name: "Non-GST",
     value:102,
  },];

//end sam

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
});

private savedItemList: ItemList[] = [];
private newPurchaseList: ItemList[] = [];
private returnList: ItemList[] = [];
private splicedList: ItemList[]=[];
private totalReturnAmount=0.0;
private totalExpAdjustment=0.0;


private validationRules = {
  selectedProfile: {
    required,
  },
};

private counterEntry: CounterEntry [] = [];

private paymentList: PaymentListType [] = [];

private item = {
  id: 0,
  profileID: 0,
  discount: 0,
  totalPaid: 0,
  totalTendered: 0,
  totalChange: 0,
  totalGrossAmt: 0,
  totalBill: 0,
  totalTax1: 0,
  totalTax2: 0,
  totalTax3: 0,
  totalTax: 0,
  description: "",
  billNo: "",
  paymentMethod: "",
  searchReceiptNo: "",
  billDate:"",
  status: "Active",
  type: "PUR",
  pType:'',
};

private v$ = useVuelidate(this.validationRules, this.state);

private viewInvoice=false;

//DEFAULT METHOD OF TYPE SCRIPT
created() {
  this.profilerService = new ProfilerService();
  this.posService = new PosService();
  this.toast = new Toaster();
  this.stockService = new StockService();

}

mounted() {
  // this.loadList();
  let rNo=this.$route.query.rn;
  // alert("receipt no "+rNo);

  if(rNo!=null && rNo!='undefined'){
    // alert(rNo);
   this.item.searchReceiptNo=rNo;
   this.viewInvoice=true;
   this.fetchReceiptNo();
   this.item.type="MDF";

  }else{
    this.item.type='PUR';

    for (var i=0;i<5;i++){
      this.openNewRow();
    }
  }
 
}

//sam 

setOtherTax(savedItem){
  //alert(event.target.value);
  //alert(savedItem.tax1);
  savedItem.tax2=savedItem.tax1;


}
public calcMargin(savedItem){
 // alert("margin" +savedItem.mrp+'....'+savedItem.purchasePrice);
 let margin="";
 if(savedItem.mrp>0 && savedItem.purchasePrice>0){
     console.log("MRP" +savedItem.mrp+'..subtot..'+this.getTheSubtotal(savedItem));

    margin = ((savedItem.mrp-this.getTheSubtotal(savedItem)/savedItem.packSize)/savedItem.mrp*100).toFixed(2);
    //savedItem.supplierBonus=margin;
    //savedItem.sellingPrice=margin;
    return margin;
 }

 return 0;  
}

//this is soumik code - fixed validItem to enable fields after product selection
public validItem(index){
  console.log("################Index " +index +"...."+this.savedItemList[index].productName+"----"+this.savedItemList[index].stockID);
  // Enable fields if product is selected (stockID > 0) OR if productName is not empty
  if(this.savedItemList[index].stockID > 0 || this.savedItemList[index].productName !== ''){
    return true;
  }
  return false;
}

public openFileUploader()
{
  //alert(" open file uoplaoded called");
  this.uploaderStatus = true;
}

updateUploaderStatus(params)
{
  this.uploaderStatus = false;
  let dataComplete=false;
  if(params.length > 0)
  {
    this.excelFileContent = [];
    //alert("parma len >0"+params[0]);
    
    this.stockService.uploadCSVFile(params[0]).then((res) => {
      //alert(" stockservice upload csv passed");

        if(res.length > 0)
        {

          let categoryID = 1;
          let bandID = 1;
          let bandSectorID = 1;
          let productTypeID = 1;
          //alert(" res.legth >0");

          res.forEach(e => {
          if( e[0] != 'H' && e[0]!='F')
          {
            

              this.excelFileContent.push(
                {
                 

                  'productName' :         e[5],
                  'genericName' :         'not specified',
                  'barcode' :             e[30], //actually storing the hsn code
                  'productType' :         productTypeID,
                  'brandName' :           bandID,
                  'brandSector' :         bandSectorID,
                  'category' :            categoryID,
                  'sideEffects' :         'side effect',
                  'stripSize' :           this.extractStripSize(e[6]), // needs working
                 // 'stripSize' :           10, // needs working
                  'packSize' :            Number(e[20]),
                  'quantity' :            Number(e[20])*this.extractStripSize(e[6]),
                  'expiryDate' :          this.createExpiryDate(e[9]), //needs working
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

              
            }else if(e[0] == 'H'){
              //Header H
              this.excelFileContent.push({
                  'productName' :         'H',
                  'genericName' :         'not specified',
                  'barcode' :             '',
                  'productType' :         1,
                  'brandName' :           1,
                  'brandSector' :         1,
                  'category' :            1,
                  'sideEffects' :         'side effect',
                  'stripSize' :           0, // needs working
                 // 'stripSize' :           10, // needs working
                  'packSize' :            0,
                  'quantity' :            0,
                  'expiryDate' :          '',
                  'packPurchasePrice' :   0,
                  'packSellingPrice' :    0,
                  'mRP' :                 0,
                  'batchNo' :             '',
                  'tax_1' :               0,
                  'tax_2':                0,
                  'tax_3' :               0,
                  'discountPercentage' :  0,
                  'description' :         'no description',
                  'minimumStock' :        Number(0),
                  'storeLocations' :      'Unknown',
                  'invoiceNo': e[2],
                  'discount':0,
                  'total': 0,
              });


            }else if(e[0] == 'F'){
              //footer
              this.excelFileContent.push({
                  'productName' :         'F',
                  'genericName' :         'not specified',
                  'barcode' :             '',
                  'productType' :         1,
                  'brandName' :           1,
                  'brandSector' :         1,
                  'category' :            1,
                  'sideEffects' :         'side effect',
                  'stripSize' :           0, // needs working
                 // 'stripSize' :           10, // needs working
                  'packSize' :            0,
                  'quantity' :            0,
                  'expiryDate' :          '', //needs working
                  'packPurchasePrice' :   0,
                  'packSellingPrice' :    0,
                  'mRP' :                 0,
                  'batchNo' :             '',
                  'tax_1' :               0,
                  'tax_2':                0,
                  'tax_3' :               0,
                  'discountPercentage' :  0,
                  'description' :         'no description',
                  'minimumStock' :        Number(0),
                  'storeLocations' :      'Unknown',
                  'invoiceNo': e[2],
                  'discount':0,
                  'total': e[15],
            });

            //data loading complete
            dataComplete = true;
          }

          if(dataComplete){
            this.SaveUploadedData();
          }
      
        }
        //save data
        //alert( "excel content "+this.excelFileContent);

     
    });
  }
}
extractStripSize(s){
  //alert(s);
  if(s.includes('ML')|| s.includes('GM')||s.includes('L')){
    //alert("contains volume");
    return '1';
  }else{
    let matches = s.match(/(\d+)/);
    //alert(matches[0]);
    return matches[0];

  }
}

createExpiryDate(s){
  //alert("creating date "+s);
  const month = s.Split["/"][0];
  const year = s.Split["/"][1];


  const formatter = new Intl.DateTimeFormat('en-US', { day: '2-digit', month: '2-digit', year: 'numeric' });

  const date = new Date("01/"+month+"/"+year);
  const formattedDate = formatter.format(date);
  //alert(formattedDate);

  return formattedDate;
  //return s.toString().replace(/^(\d{2})(\d{2})(\d{4})$/, '$1-$2-$3');


}

reverseExpiryDate(s){
  //var s="2005-02-11";
  console.log("expiry date "+s);
  console.log("split "+s.split('-'));
  const year = s.split("-")[0];
  console.log("year : "+year);
  const month=s.split("-")[1];
  console.log("month "+month);
  const shortYear = year.substring(2);
  console.log("short year "+shortYear);

  const finalDate = month+"/"+shortYear;
  console.log("formatted date : "+finalDate);

  return finalDate;
  
}
SaveUploadedData()
{
  //alert("Saving" +JSON.stringify(this.excelFileContent));
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

    // if (
    //   v.productName == null ||
    //   v.batchNo == null || v.batchNo == "" ||
    //   v.productType == 0 || v.brandName == 0 ||  v.brandSector == 0 ||
    //   v.category == 0 || v.packSize <= 0 || v.quantity <= 0
    //   v.productName == null ||
    //   v.batchNo == null || v.batchNo == "" ||
    //   v.productType == 0 ||  v.packSize <= 0 || v.quantity <= 0
    //   ) {
    //   this.totalInValid++;
    //   invalidListItems.push(index);
    // }
    // else
    // {
    //    this.totalValid++;
    // }
  });
  //return invalidListItems;
  return [];
}

//END SAM
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
  console.log('Search item called with query:', event.query);
  setTimeout(() => {
    this.posService.searchItem(event.query.trim()).then((data) => {
      console.log('Search response:', data);
      this.itemList = data.records || [];
      if (data.error) {
        console.error('Search error:', data.error);
        this.toast.showError('Search failed: ' + data.error);
      }
    }).catch((error) => {
      console.error('Search request failed:', error);
      this.toast.showError('Failed to search items. Please try again.');
      this.itemList = [];
    });
  }, 200);
}

saveProfile(event) {
  const profileInfo = event.value;
  this.state.selectedProfile = profileInfo.account_title;
  this.item.profileID = profileInfo.id;
  this.selectedSupplier=profileInfo.id;
  // alert("Save profile "+JSON.stringify(this.item));

}

 checkReturnItem(sitem){
    console.log(">>>> CHECK RETURN ITEM >>>"+JSON.stringify(sitem)+"____"+sitem.preturn);
    
    console.log("IS preturn : "+(sitem.preturn>0));
    
   
}

adjustReturnPricing(index){
  var i=0;
  this.splicedList=[];
  this.totalReturnAmount=0;
  var markedCheckbox = document.getElementsByName('pl');  
  // alert("Adjust pricing "+markedCheckbox.length);

  for (var checkbox of markedCheckbox) {  
    if (checkbox.checked)  {
      //document.body.append(checkbox.value + ' '); 
      let item = this.returnList[i];
      // item.isSelected=1;
      this.totalReturnAmount =this.totalReturnAmount+ Number(item.subTotal);
      //alert("Checked Index "+item.subTotal); 
      this.splicedList.push(item);

    }else{
      console.log("Ignore index "+i);
    }

    i++;
    
  }
  
  console.log("FILTERED RETURN LIST  "+JSON.stringify(this.splicedList));
}

// selectReturnItem(index){
//   const item = this.returnList[index];
//   let isChecked = document.getElementById(item.stockID).checked;
//   //console.log(" index "+index+" Current Return total "+this.totalReturnAmount+".. ischecked "+isChecked+" sub total "+item.subTotal);
//   //alert(item.preturn);
//   if(item.preturn==1){

//     if(isChecked){
//      item.isSelected=1;
//     // alert(' selected');
//      // this.totalReturnAmount=this.totalReturnAmount+Number(item.subTotal);

//     }else{
//       item.isSelected=0;
//       //alert(item.subTotal);
//       //alert(' un-selected');
//       //this.totalReturnAmount=this.totalReturnAmount-Number(item.subTotal);

//     }
//   }
//  // alert(this.totalReturnAmount);
//  console.log("")
// }



openNewRow(){
 // alert('new row add');
  this.profileStatus=false;
  
  
  this.savedItemList.push({
    mode: "Strip",
    stockID: 0,
    productID: 0,
    productName: '',
    generic: '',
    itemDescription: '',
    leaf:0,
    unit: 1,
    totalUnit: 0,
    stockQty: 0,
    freeUnit: 0,
    supplierBonus: 0,
    batchNo: '',
    packSize: 0,
    sheetSize: 0,
    purchasePrice: 0,
    orginalSPrice: 0,
    sellingPrice: 0,
    mrp: 0,
    brandName: '',
    sectorName: '',
    categoryName: '',
    productType: '7898765432',
    expiryDate: '',
    cusDisc: 0,
    purchaseAfterDisc: 0,
    itemDisc: 0,
    specialDisc:0,
    tax1: 0,
    tax2: 0,
    tax3: 0,
    subTotal: 0,
    //invoiceNo: '100',
    //discount:150,
    //total: 300,
    preturn:0,
    isSelected:0,
    producType:0,
    //this is soumik code - added HSN field for new rows
    hsn: 0,
    
  });
//alert("open new row "+JSON.stringify(this.savedItemList));
}


//this is soumik code - fixed product selection issue
saveItem(event, index) {
  const itemInfo = event.value;
  console.log('Selected item:', itemInfo);
  
  // Check if itemInfo is valid
  if (!itemInfo || typeof itemInfo === 'string') {
    console.error('Invalid item selection');
    return;
  }
  
  // Check if this is a requested item
  if (itemInfo.is_requested == 1) {
    // For requested items, set minimal data
    this.savedItemList[index]={
      mode: "Pack",
      stockID: 0,
      productID: 0,
      productName: itemInfo.product_name,
      generic: '',
      itemDescription: 'Requested Item',
      barcode: 0,
      unit: 1,
      totalUnit: 0,
      stockQty: 0,
      freeUnit: 0,
      supplierBonus: 0,
      batchNo: '',
      packSize: 1,
      sheetSize: 1,
      purchasePrice: 0,
      orginalSPrice: 0,
      sellingPrice: 0,
      mrp: 0,
      brandName: '',
      sectorName: '',
      categoryName: '',
      productType: '100',
      expiryDate: '01/30',
      cusDisc: 0,
      purchaseAfterDisc: 0,
      itemDisc: 0,
      specialDisc:0,
      tax1: 6,
      tax2: 6,
      tax3: 0,
      subTotal: 0,
      preturn:0,
      isSelected:0,
      hsn: 0,
    };
  } else {
    // For stock items, use existing logic
    this.savedItemList[index]={
      mode: "Pack",
      stockID: itemInfo.id,
      productID: itemInfo.product_id,
      productName: itemInfo.product_name,
      generic: itemInfo.generic,
      itemDescription: itemInfo.description,
      barcode: Number(itemInfo.barcode),
      unit: 1,
      totalUnit: 0,
      stockQty: Number(itemInfo.qty),
      freeUnit: 0,
      supplierBonus: 0,
      batchNo: itemInfo.batch_no,
      packSize: Number(itemInfo.pack_size),
      sheetSize: Number(itemInfo.strip_size),
      purchasePrice: Number(itemInfo.purchase_price),
      orginalSPrice: Number(itemInfo.sale_price),
      sellingPrice: Number(itemInfo.sale_price),
      mrp: Number(itemInfo.mrp),
      brandName: itemInfo.bName,
      sectorName: itemInfo.bSector,
      categoryName: itemInfo.cName,
      productType: itemInfo.pType,
      expiryDate : this.reverseExpiryDate(itemInfo.expiry_date),
      cusDisc: Number(itemInfo.discount_percentage),
      purchaseAfterDisc: 0,
      itemDisc: 0,
      specialDisc:0,
      tax1: Number(itemInfo.tax_1),
      tax2: Number(itemInfo.tax_2),
      tax3: Number(this.calcMargin(itemInfo)),
      subTotal: 0,
      preturn:0,
      isSelected:0,
      hsn: itemInfo.hsn || itemInfo.barcode || 0,
    };
  }

  //this is soumik code - clear itemList and blur input to close dropdown
  this.itemList = [];
  this.itemScanBox = "";
  
  // Force blur the autocomplete input to close dropdown
  setTimeout(() => {
    const autoCompleteInputs = document.querySelectorAll('.p-autocomplete-input');
    autoCompleteInputs.forEach(input => {
      if (input) {
        input.blur();
      }
    });
  }, 100);
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

    this.currentUserID = data.currentUserID;
    this.storeName = data.storeName;
  });
}

//sam
getPAD(data){
  console.log(data.specialDisc);

 // return data.purchaseAfterDisc>0 ? data.purchaseAfterDisc:data.purchasePrice;
 let d1 = data.purchasePrice * (100-data.cusDisc)/100;
 console.log('d1--'+d1);
 let d2 = d1 * (100-data.specialDisc)/100;
  //return data.purchasePrice * (100-data.itemDisc)/100;
  console.log('d2--'+d2);

  return d2;
}

 setGSTOption(){
  //alert("gst called"+this.item.productType);
  this.savedItemList.forEach((e) => {
      e.productType=this.item.pType;
      console.log('PRODUCT TYPE : '+e.productType);
      if(Number(e.productType)==101){
        this.taxNames[0].show=false;
        this.taxNames[1].show=false;

      }else{
        this.taxNames[0].show=true;
        this.taxNames[1].show=true;

      }
    })
}

getTheSubtotal(data) {
 // alert('getting sub total'+data.specialDisc);
  //sam
        const type = data.productType;
        //const qty = Number(data.unit);
        //const qty = Number(data.sheetSize*data.packSize);
        const qty = Number(data.packSize);

        console.log('strip size : '+data.sheetSize+' pack size '+data.packSize +' quantity: '+qty);
        const price = Number(data.purchasePrice);
        const discount = Number(data.itemDisc);
        const specialDisc = Number(data.specialDisc);
        const mrp   = Number(data.mrp);

        console.log("item disc>>>>>>>"+discount+"special dis>>>"+specialDisc+"cus disc>>>"+Number(data.cusDisc));

        const tax1 = data.tax1;
        const tax2 = data.tax2;
        const tax3 = data.tax3;
        const totalTax = tax1 + tax2 + tax3;
        //const totalTax = tax1 + tax2;


        //sam: total price
        const totalPrice = price*data.packSize;
        console.log('total price'+totalPrice);

        const discountPrice = this.getPAD(data);
        console.log('price after discount'+discountPrice);
        data.purchaseAfterDisc = discountPrice;
        //data.description = this.calcMargin(data);
        //add GST to it
        const withGST = discountPrice * (100+totalTax)/100;
        //const finalAmount = price*data.packSize;
        const finalAmount = withGST*data.packSize;
        console.log('final amount : '+finalAmount);

        const margin = ((data.mrp-finalAmount/data.packSize)/data.mrp*100).toFixed(2);
        data.description = margin;

        data.sellingPrice = mrp;
        data.subTotal = finalAmount.toFixed(2);
        console.log("EVALUATING TOTAL RETURN AMOUNT "+data.isSelected);
        if(data.isSelected==1){
          this.totalReturnAmount = this.totalReturnAmount + Number(finalAmount.toFixed(2));
        }
       console.log("TOTAL RETURN AMOUNT IN SUBTOTAL "+this.totalReturnAmount);

        return finalAmount.toFixed(2);


}

getReturnSubtotal(data, flag) {
 // alert('getting sub total'+data.specialDisc);
  //sam
        const type = data.productType;
        //const qty = Number(data.unit);
        //const qty = Number(data.sheetSize*data.packSize);
        const qty = Number(data.packSize);

        console.log('strip size : '+data.sheetSize+' pack size '+data.packSize +' quantity: '+qty);
        const price = Number(data.purchasePrice);
        const discount = Number(data.itemDisc);
        const specialDisc = Number(data.specialDisc);
        const mrp   = Number(data.mrp);

        console.log("item disc>>>>>>>"+discount+"special dis>>>"+specialDisc+"cus disc>>>"+Number(data.cusDisc));

        const tax1 = data.tax1;
        const tax2 = data.tax2;
        const tax3 = data.tax3;
        const totalTax = tax1 + tax2 + tax3;
        //const totalTax = tax1 + tax2;


        //sam: total price
        const totalPrice = price*data.packSize;
        console.log('total price'+totalPrice);

        const discountPrice = this.getPAD(data);
        console.log('price after discount'+discountPrice);
        data.purchaseAfterDisc = discountPrice;
        //data.description = this.calcMargin(data);
        //add GST to it
        const withGST = discountPrice * (100+totalTax)/100;
        //const finalAmount = price*data.packSize;
        let finalAmount = withGST*data.packSize;
        console.log('final amount : '+finalAmount);

        const margin = ((data.mrp-finalAmount/data.packSize)/data.mrp*100).toFixed(2);
        data.description = margin;

        data.sellingPrice = mrp;
        data.subTotal = finalAmount.toFixed(2);
        let el = document.getElementById(data.stockID)
        // if(el !=null && el.checked){
        //   console.log("EVALUATING TOTAL RETURN AMOUNT FOUND WITH TOTAL RETURN AMOUNT PRESET "+this.totalReturnAmount);

        //  this.totalReturnAmount=100;
        // }
       console.log("TOTAL RETURN AMOUNT IN RETURN SUBTOTAL "+this.totalReturnAmount+" FINAL AMOUNT "+finalAmount);

        return finalAmount.toFixed(2);


}

 get expiryAdjustment(){
  // alert(document.getElementById('expadjust')?.value)
  // return Number(document.getElementById('expadjust')?.value);
  return this.totalExpAdjustment;
}

get totalGross() {
  let total = 0;
  this.savedItemList.forEach((e) => {
    //total = total + e.purchasePrice * e.unit;//sam
    console.log(">>GROSS CALC "+e.packSize+'....'+e.purchasePrice+'....e subtotal '+e.subTotal+"....preturn "+e.preturn);

    // sam on 25/8
    if(e.preturn==0){
      total = total +e.packSize*e.purchasePrice;

    }else if(e.preturn==1){
       total = total-e.packSize*e.purchasePrice;

    }
  });

  console.log("GROSS CALCULATED AS >>>"+total);

  return total;
}

get totalTax1() {
  let total = 0;

  this.savedItemList.forEach((e) => {

   // if(e.preturn==0){
        const tax = e.tax1;
        const price = e.purchasePrice * e.packSize;
        const afterDisc = (price / 100) * e.itemDisc;
        const priceAfterDisc = price -afterDisc;
        console.log("***** DISCOUNT IN TAX 1 IS>>"+e.itemDisc);
        console.log("*****AFTER DISCOUNT IN TAX 1 IS>>"+priceAfterDisc);

        const afterSpecialDiscount = priceAfterDisc - (priceAfterDisc/100)*e.specialDisc;
        //total = total + ((price - afterDisc) / 100) * tax;
        console.log("*****AFTER SPECIAL DISCOUNT IN TAX 1 IS >>"+afterSpecialDiscount);
        console.log("****FROM PAD CALCULATION >>"+this.getPAD(e));
        if(e.preturn==0){
          total =total + (e.packSize)*(this.getPAD(e))*tax/100;
        }else if(e.preturn==1){
          total =total - (e.packSize)*(this.getPAD(e))*tax/100;

        }
   // }

    // total =total + (afterSpecialDiscount)*tax/100;
    // total = total + ((total - specialDisc) / 100) * tax;
  });

  return Number(total.toFixed(2));
}

get totalTax2() {
  let total = 0;
  this.savedItemList.forEach((e) => {
   // if(e.preturn==0){

        const tax = e.tax2;
        const price = e.purchasePrice * e.packSize;
        const afterDisc = (price / 100) * e.itemDisc;
        console.log("*****AFTER DISCOUNT IN TAX 2"+afterDisc);
        const priceAfterDisc = price -afterDisc;
        const afterSpecialDiscount = priceAfterDisc - (priceAfterDisc/100)*e.specialDisc;
        //total = total + ((price - afterDisc) / 100) * tax;
      // total =total + (afterSpecialDiscount/100)*tax;
      if(e.preturn==0){
          total =total + (e.packSize)*(this.getPAD(e))*tax/100;
        }else if(e.preturn==1){
          total =total - (e.packSize)*(this.getPAD(e))*tax/100;

        }
        // const specialDisc = e.specialDisc;
        // total = total + ((total - specialDisc) / 100) * tax;
    //}
  });

  return Number(total.toFixed(2));
}

get totalTax3() {
  let total = 0;
  this.savedItemList.forEach((e) => {
    const tax = e.tax3;
    const price = e.purchasePrice * e.packSize;
    const afterDisc = (price / 100) * e.itemDisc;
    total = total + ((price - afterDisc) / 100) * tax;
  });

  return Number(total.toFixed(2));
}

get totalDiscAmount() {
  let total = 0;
  this.savedItemList.forEach((e) => {
    const price = e.purchasePrice * e.packSize;
    total = total + (price / 100) * e.cusDisc;
    //sam
    total = total +( (price-total)/100)*e.specialDisc;


  });
  
  return total;
}

get netTotal() {
  
  let mynet=0;
  this.savedItemList.forEach((e) => {
    mynet = mynet+Number(e.subTotal);
  });
  console.log("EXPIRRY ADJUST "+this.expiryAdjustment);

  mynet = mynet-this.totalReturnAmount-this.expiryAdjustment;
  console.log("MY NET "+mynet);
  return Math.round(mynet);
}

fixDigits(amt) {
  let total = 0;

  if (amt != null) {
    total = amt.toFixed(2);
  }
  return total;
}

clearAll() {
  
  this.savedItemList = [];
  this.paymentList = [];
  this.returnList=[];

  this.item = {
    id: 0,
    profileID: 0,
    discount: 0,
    totalGrossAmt: 0,
    totalBill: 0,
    totalPaid: 0,
    totalTendered: 0,
    totalChange: 0,
    totalTax1: 0,
    totalTax2: 0,
    totalTax3: 0,
    totalTax: 0,
    description: "",
    billNo: "",
    billDate:"",
    searchReceiptNo: "",
    paymentMethod: "",
    status: "Active",
    type: this.item.type,
  };

  this.itemScanBox = "";
  this.state.selectedProfile = "";
}

formatExpiryDate(d) {
  return moment(d).format("MMM-YYYY");
}

clearListItem(item) {
  this.savedItemList.splice(this.savedItemList.indexOf(item), 1);
  this.toast.showSuccess("Item Deleted Successfully");
}

clearReturnItem(item) {
  this.returnList.splice(this.returnList.indexOf(item), 1);
  this.toast.showSuccess("Item Deleted Successfully");
}

limitString(str) {
  if (str.length > 40) str = str.substring(0, 40) + "...";
  return str;
}

closePaymentScreen() {
  this.paymentDialog = false;
}

getProceededPayments(paymentList) {
  this.paymentList = paymentList;
  //const tenderedList = this.getTotalPaid(paymentList);
  const tenderedList = 0;
  this.item.totalPaid = Number(tenderedList[0]);
  this.item.totalTendered = Number(tenderedList[1]);
  this.item.totalChange = Number(tenderedList[2]);

  //const method = this.getPaymentMethod(paymentList);
  const method = "Pay Later";
  this.item.totalPaid = 0;

  this.item.totalTendered=0;
  this.item.totalChange=0;


  this.item.paymentMethod = method;

  this.item.discount = this.totalDiscAmount;
  this.item.totalGrossAmt = this.totalGross;
  this.item.totalBill = this.netTotal;
  this.item.totalTax1 = this.totalTax1;
  this.item.totalTax2 = this.totalTax2;
  this.item.totalTax3 = this.totalTax3;
  this.item.totalTax = Number(
    this.totalTax1 + this.totalTax2 + this.totalTax3
  );
  //alert("now payment" +JSON.stringify(paymentList));
  // alert(' in save purchase item '+this.item.type);
  this.setAccountingEntries();

  //sam
  //paymentList.push();

  // paymentList.push({
  //       paymentType: "Cash",
  //       accountNo: "",
  //       transTotalAmount: this.item.totalPaid,
  //       terminalId: "Manual",
  //       authCode: "",
  //       hostResponse: "",
  //       transId: "",
  //       // transStatus: this.transStatus,
  //       // transType: this.itemSource,
  //       transDate: "",
  //       transTime: "",
  //       transAmount: this.item.totalBill,
  //       transRef: "",
  //       entryMode: "",
  //       giftCardRef: "",
  //       cardBalance: "",
  //       tendered: 0,
  //       change: 0,
  //       roundOff: 0,
  //       bankID: 0,
  //     });
  //alert("counter entry "+JSON.stringify(this.counterEntry));
  //alert("payment list "+JSON.stringify(this.paymentList));
 console.log(">>>>TOTAL ITEM LIST "+JSON.stringify(this.savedItemList));

 //separate purchases and returns

//  this.savedItemList.forEach((e) => {
//     if(e.preturn==0){
//       this.newPurchaseList.push(e);
//     }else if(e.preturn==1 && e.isSelected==1){
//       this.returnList.push(e);
//     }

//  })

 console.log(">>>>NEW PURCHASE LIST >>>"+JSON.stringify(this.savedItemList));
 console.log(">>>>RETURN  LIST >>>"+JSON.stringify(this.splicedList));


  //save the new purchase list
  // this.item.type='PUR';
  this.posService
    .savePurchaseItem(this.item, this.paymentList, this.savedItemList, this.counterEntry)
    .then((res) => {
      if (res.alert == "info") {
        //this.clearAll();
      }

      this.toast.handleResponse(res);
    });

    //ignore the unselected return items
    // this.returnList = this.returnList.filter(function (item) {
    //     return item.isSelected==1;
    // });
    
    console.log("FILTERED RETURN LIST "+this.splicedList+" length : "+this.splicedList.length);


    //save the returned item
    if (this.splicedList.length>0) {
       this.item.type='RPU'

       //create a payment
       this.paymentList=[];
       paymentList.push({
        paymentType: "Cash",
        accountNo: "",
        transTotalAmount: this.totalReturnAmount,
        terminalId: "Manual",
        authCode: "",
        hostResponse: "",
        transId: "",
        // transStatus: this.transStatus,
        // transType: this.itemSource,
        transDate: "",
        transTime: "",
        transAmount: this.totalReturnAmount,
        transRef: "",
        entryMode: "",
        giftCardRef: "",
        cardBalance: "",
        tendered: 0,
        change: 0,
        roundOff: 0,
        bankID: 0,
      });
       this.posService
      .savePurchaseItem(this.item, this.paymentList, this.splicedList, this.counterEntry)
      .then((res) => {
        if (res.alert == "info") {
          //this.clearAll();
        }

        this.toast.handleResponse(res);
      });
    }
  this.clearAll();
  this.paymentDialog = false;
  this.submitted = false;
}

openPaymentMethod(isFormValid) {

  // alert("form valid "+isFormValid);
  this.submitted = true;
  if ((isFormValid = true)) {
    // this.paymentDialog = true;
    // this.store.dispatch(
    //   ActionTypes.TOTAL_BILL,
    //   Number(this.fixDigits(this.netTotal))
    // );

    //sam
    this.getProceededPayments(this.paymentList);

  }
}

get totalPaidCash()
{
  let total = 0;

  this.paymentList.forEach(e => {
    if(e.paymentType == 'Cash')
    {
      total = total + e.transTotalAmount;
    }
  });

  return total;
}

get totalPaidBank()
{
  let total = 0;

  this.paymentList.forEach(e => {
    if(e.paymentType != 'Cash')
    {
      total = total + e.transTotalAmount;
    }
  });

  return total;
}

getTotalPaid(paymnetList) {
  let totalPaid = 0;
  let totalTendered = 0;
  let totalChange = 0;

  paymnetList.forEach((e) => {
    if (e.paymentType != "Tip") {
      totalPaid = totalPaid + Number(e.transTotalAmount);
      totalTendered = totalTendered + Number(e.tendered);
      totalChange = totalChange + Number(e.change);
    }
  });

  return [totalPaid, totalTendered, totalChange];
}

get totalBalance()
{
  return this.netTotal - this.item.totalPaid;
}

getPaymentMethod(paymnetList) {
  let method = "";

  if (paymnetList.length == 0) {
    method = "Pay Later";
  } else if (paymnetList.length == 1) {
    method = paymnetList[0].paymentType;
  } else if (paymnetList.length > 1) {
    method = "Split";
  }

  return method;
}

clearFreeUnit() {
  //this.toast.showSuccess("Mode Changed Successfully");

  if (this.item.type == "RPU") {
    this.receiptDailog = true;
    this.viewInvoice=false;
    document.getElementById("bNo").style.display="block";

  }else if(this.item.type=='PUR'){
    this.viewInvoice=false;
    document.getElementById("bNo").style.display="block";

    this.clearAll();

  }else if(this.item.type=='CHAL'){
    // alert("challan clicked");
    document.getElementById("bNo").style.display="none";
    
  }
  this.submitted = false;
}

fetchReceiptNo() {

   //before adding the receipt items, clear the empty rows
   this.savedItemList.forEach((e) => {
      console.log(e);
        if(e.productID==0){
        this.clearListItem(e);

        }
  })

  //  alert('SEARCH RECEIPT NO : '+this.item.searchReceiptNo);
  this.posService.getPurchaseItems(this.item.searchReceiptNo).then((data) => {

    if (data.receipt != null) {
         //alert(JSON.stringify(data));

      //alert("global supp id "+this.selectedSupplier);
      // this.item.profileID=this.selectedSupplier;
    //  alert("return item profile id "+this.item.profileID);
      this.state.selectedProfile = data.receipt.profile_name.accountName;
     // alert(this.state.selectedProfile);
      this.item.profileID = data.receipt.profile_name.id;
      this.item.billNo =
        data.receipt.bill_no == null
          ? ""
          : data.receipt.bill_no;
      this.item.description =
        data.receipt.description == null ? "" : data.receipt.description;
        this.item.billDate=moment(data.receipt.receipt_date).format("DD/MM/YYYY");
    }

    if (data.receiptItems != null) {
     // alert('receipt items found');
      if(this.viewInvoice==false){
        //alert('view invoice flag is false');
        data.receiptItems.forEach((e) => {
          this.item.pType = e.product_type;

        this.returnList.push({
          mode: e.mode,
          stockID: Number(e.stock_id),
          productID: Number(e.product_id),
          productName: e.item_name,
          generic: e.generic_name,
          itemDescription: e.item_description,
          unit: Number(e.unit),
          totalUnit: Number(e.total_unit),
          stockQty: Number(e.stock_detail.qty),
          freeUnit: Number(e.free_unit),
          supplierBonus: Number(e.supplier_bonus),
          batchNo: e.batch_no,
          packSize: Number(e.pack_size),
          sheetSize: Number(e.sheet_size),
          purchasePrice: Number(e.purchase_price),
          orginalSPrice: Number(e.stock_detail.sale_price),
          sellingPrice: Number(e.selling_price),
          mrp: Number(e.mrp),
          brandName: e.brand_name,
          sectorName: e.sector_name,
          categoryName: e.category_name,
          productType: e.product_type,
          expiryDate: this.reverseExpiryDate(e.expiry_date),
          itemDisc: Number(e.purchase_disc),
          purchaseAfterDisc: Number(e.after_disc),
          cusDisc: Number(e.item_disc),
          tax1: Number(e.tax_1),
          tax2: Number(e.tax_2),
          tax3: Number(e.tax_3),
          subTotal: Number(-e.sub_total),
          leaf:0,
          specialDisc:0,
          preturn:1,
          isSelected:0,
          hsn:e.hsn,
          barcode:e.barcode
        });
      ``});
      }else{
       // alert('view invoice flag is true');
        data.receiptItems.forEach((e) => {
        // alert(JSON.stringify(e));
          this.item.pType = e.product_type;
        this.savedItemList.push({
          mode: e.mode,
          stockID: Number(e.stock_id),
          productID: Number(e.product_id),
          productName: e.item_name,
          generic: e.generic_name,
          itemDescription: e.item_description,
          unit: Number(e.unit),
          totalUnit: Number(e.total_unit),
          stockQty: Number(e.stock_detail.qty),
          freeUnit: Number(e.free_unit),
          supplierBonus: Number(e.supplier_bonus),
          batchNo: e.batch_no,
          packSize: Number(e.pack_size),
          sheetSize: Number(e.sheet_size),
          purchasePrice: Number(e.purchase_price),
          orginalSPrice: Number(e.stock_detail.sale_price),
          sellingPrice: Number(e.selling_price),
          mrp: Number(e.mrp),
          brandName: e.brand_name,
          sectorName: e.sector_name,
          categoryName: e.category_name,
          productType: e.product_type,
          expiryDate: this.reverseExpiryDate(e.expiry_date),
          itemDisc: Number(e.purchase_disc),
          purchaseAfterDisc: Number(e.after_disc),
          cusDisc: Number(e.item_disc),
          tax1: Number(e.tax_1),
          tax2: Number(e.tax_2),
          tax3: Number(e.tax_3),
          subTotal: Number(-e.sub_total),
          leaf:0,
          specialDisc:0,
          preturn:1,
          isSelected:0,
          hsn:e.hsn,
          barcode:e.barcode
        });
      });
      }
    

      this.receiptDailog = false;
      // alert('end ' +this.item.profileID);

    }
  });

 


}

openProfileDialog() {
  // alert('new item adding from pospurchase');
  this.dialogTitle = "Add New Item";
  this.profileStatus = true;
  this.statusType = "New";
}

updateProfilerStatus(res) {
  this.profileStatus = false;

}


redirectHome() {
  router.replace({ path: "/store/dashboard", params: {} });
}

get countTaxesLen()
{
  let ctr = 0;

  if(this.taxNames[0].show)
  {
    ctr++;
  }

  if(this.taxNames[1].show)
  {
    ctr++;
  }

  if(this.taxNames[2].show)
  {
    ctr++;
  }

  return Number(ctr + 12);
}

setAccountingEntries() {
 
  this.counterEntry = [];

  if (this.item.type == "PUR") {

    this.counterEntry.push({
        accountID: 3,
        accountHead: 'Inventory',
        amount: this.totalGross-this.totalDiscAmount,
        type: "Debit",
    });

    //ADD EXPIRY ADJUSTMENT
    this.counterEntry.push({
        accountID: 3,
        accountHead: 'Expiry Adjustment',
        amount: this.totalExpAdjustment,
        type: "Credit",
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
    } else if(this.totalBalance > 0 && this.item.totalPaid > 0) {
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
    
  } else if(this.item.type == "RPU") {

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
    } else if(this.totalBalance > 0 && this.item.totalPaid > 0) {
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
        accountID: 3,
        accountHead: 'Inventory',
        amount: this.totalGross-this.totalDiscAmount,
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
  }
  else
  {
    //NOTHING
  }
}

get currency() {
  return this.store.getters.getCurrency;
}
}
</script>

<style scoped>
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
</style>