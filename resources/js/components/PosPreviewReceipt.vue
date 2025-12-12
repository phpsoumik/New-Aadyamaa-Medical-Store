<template>
	<Dialog
		id="previewReceiptDailog"
		v-model:visible="productDialog"
		:style="{ width: '100vw' }"
		position="top"
		class="p-fluid p-m-0 p-dialog-maximized"
		:modal="true"
		:closable="true"
		@hide="closeDialog"
	>
		<template #header>
			<h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
				<i class="pi pi-eye"></i> {{ receiptDialogName }}
			</h5>
		</template>
		 <div class="p-d-flex p-jc-between">
				<div>
						<h3>{{items.storeName}}</h3>
						<h6>{{items.storeAddress}}</h6>
						<h6>{{items.storeEmail}}</h6>
						<h6>{{items.storePhone}}</h6>
						<h6>License No : {{items.storeLicense}}</h6>
				</div>
				<div>
						<img  class="company-logo" :src="getCompanyURL()" alt="Company Logo" />
				</div>
			</div>  
			<h3 class="p-mb-2 p-mt-1 p-text-bold p-text-uppercase" style="color:#004C97">{{receiptTypeName}}</h3> 
			<div class="p-grid">
				<div class="p-col"> 
					<span class="p-mr-2 p-text-uppercase ">  <b> To : </b> {{items.selectedProfile}}</span>
					<span class="p-mx-2 p-text-uppercase ">  <b> Receipt NO : </b> {{items.receiptNo}} </span>
					<span class="p-mx-2 p-text-uppercase ">  <b> Receipt DATE : </b> {{formatDate(items.receiptDate)}} / {{formatTime(items.createdDate)}} </span>
					<span v-if="items.returnReceipt != null" class="p-mx-2 p-text-uppercase ">  <b> REF NO : </b> {{items.returnReceipt}} </span>
					<span v-if="items.billNo != null && (items.type == 'PUR' || items.type == 'RPU')" class="p-mx-2 p-text-uppercase ">  <b> Bill NO : </b> {{items.billNo}} </span>
					<span v-if="transferStoreName != '' && items.type == 'TRN'" class="p-mx-2 p-text-uppercase ">  <b> TRANSFER TO : </b> {{transferStoreName}} </span>
				</div>
			</div>
			<div class="p-grid">
				<div class="p-col">
						<DataTable :value="itemList"  
							class="p-datatable-sm p-datatable-gridlines"
							responsiveLayout="scroll"
						>
								<Column style="width: 5%;"  class="p-p-1" v-if="checkOptionalCol('Mode')" field="mode" header="Mode"></Column>
								<Column style="width: 10%" class="p-p-1" field="itemName" header="PRODUCT"></Column>
								<Column style="width: 10%" class="p-p-1" field="genericName" header="GENERIC"></Column>
								<Column style="width: 8%"  class="p-p-1"  v-if="checkOptionalCol('Batch NO')" field="batchNo" header="BATCH NO"></Column>
								<Column style="width: 10%" class="p-p-1" v-if="checkOptionalCol('Brand Name')" field="brandName" header="BRAND"></Column>
								<Column style="width: 10%" class="p-p-1" v-if="checkOptionalCol('Brand Sector')" field="sectorName" header="BRAND SECTOR"></Column>
								<Column style="width: 10%" class="p-p-1" v-if="checkOptionalCol('Category')" field="categoryName" header="CATEGORY"></Column>
								<Column style="width: 10%" class="p-p-1" v-if="checkOptionalCol('Product Type')" field="productType" header="PRODUCT TYPE"></Column>
								<Column style="width: 5%"  class="p-p-1" header="Expiry">
									 <template #body="slotProps">
										{{
											expiryDate(
												slotProps.data.expiryDate
											)
										}}
									</template>
								</Column>
								<Column style="width: 5%"  class="p-p-1" v-if="checkOptionalCol('Pack Size')" field="packSize" header="PACK SIZE"></Column>
								<Column style="width: 5%"  class="p-p-1" v-if="checkOptionalCol('Strip Size')" field="sheetSize" header="STRIP SIZE"></Column>
								<Column style="width: 5%"  class="p-p-1" field="unit" header="UNITS"></Column>
								<Column style="width: 5%" v-if="items.type == 'TRN'" class="p-p-1" field="freeUnit" header="GFT UNITS"></Column>
								<Column style="width: 5%"  class="p-p-1" field="totalUnit" header="TOTAL UNITS"></Column>
								<Column style="width: 5%"  class="p-p-1"  v-if="items.type == 'PUR' || items.type == 'RPU'" field="freeUnit" header="FREE QTY"></Column>
								<Column style="width: 10%"  class="p-p-1" v-if="items.type == 'PUR' || items.type == 'RPU'" field="supplierBonus" header="SUP BONUS"></Column>
								<Column v-if="items.type == 'INE' || items.type == 'RFD' || items.type == 'TRN'"  style="width: 5%" class="p-p-1" header="PRICE">
									<template #body="slotProps">
										{{currency}} {{
											fixDigits(
												slotProps.data.sellingPrice
											)
										}}
									</template>
								</Column>
								<Column v-if="items.type == 'PUR' || items.type == 'RPU'" style="width: 5%" class="p-p-1"  header="MRP">
									<template #body="slotProps">
										{{currency}} {{
											fixDigits(
												slotProps.data.mrp
											)
										}}
									</template>
								</Column>
								<Column style="width: 10%" v-if="items.type == 'PUR' || items.type == 'RPU'" class="p-p-1" header="PURCHASE PRICE">
									<template #body="slotProps">
										{{currency}} {{
											fixDigits(
												slotProps.data.purchasePrice
											)
										}}
									</template>
								</Column>
								<Column style="width: 5%" class="p-p-1" v-if="checkOptionalCol('Cus Disc')"  header="CUS DISC">
									<template #body="slotProps">
										{{
											fixDigits(
												slotProps.data.itemDisc
											)
										}} %
									</template>
								</Column>
								
								<Column style="width: 5%" class="p-p-1" v-if="items.type == 'PUR' || items.type == 'RPU'" header="MFG DISC">
									<template #body="slotProps">
										{{
											fixDigits(
												slotProps.data.purchaseDisc
											)
										}} %
									</template>
								</Column>
								<Column v-if="checkOptionalCol(taxNames[0].taxName)" style="width: 5%" class="p-p-1" :header="taxNames[0].taxName">
									<template #body="slotProps">
										{{
											fixDigits(
												slotProps.data.tax1
											)
										}} %
									</template>
								</Column>
								<Column v-if="checkOptionalCol(taxNames[1].taxName)" style="width: 5%" class="p-p-1"  :header="taxNames[1].taxName">
									<template #body="slotProps">
										{{
											fixDigits(
												slotProps.data.tax2
											)
										}} %
									</template>
								</Column>
								<Column v-if="checkOptionalCol(taxNames[2].taxName)" style="width: 5%" class="p-p-1" :header="taxNames[2].taxName">
									<template #body="slotProps">
										{{
											fixDigits(
												slotProps.data.tax3
											)
										}} %
									</template>
								</Column>
								<Column style="width: 6%" class="p-p-1"  header="AMOUNT">
									<template #body="slotProps">
										{{currency}} {{
											fixDigits(
												slotProps.data.subTotal
											)
										}}
									</template>
								</Column>
						</DataTable>
				</div>
			</div>
			<div class="p-m-0" v-if="items.description      != null    && items.description     != ''"><small>Description : {{ items.description }}</small></div>
			<div class="p-m-0" v-if="items.doctorDetails    != null    && items.doctorDetails   != ''"><small>Doctor Details : {{ items.doctorDetails }}</small></div>
			<div class="p-m-0" v-if="items.patientDetails   != null    && items.patientDetails  != ''"><small>Patient Details : {{ items.patientDetails }}</small></div>
			<div class="p-grid">
				<div class="p-col-12">
					<table class="table table-bordered total-lables">
						<tr>
							<td>Total Gross : {{currency}} {{ fixDigits(items.totalGrossAmt) }}</td>
							<td>Total Disc : {{currency}} {{ fixDigits(items.discount) }}</td>
							<td v-if="taxNames[0].show == true">
								Total {{ taxNames[0].taxName }} : {{currency}} {{ fixDigits(items.totalTax1) }}
							</td>
							<td v-if="taxNames[1].show == true">
								Total {{ taxNames[1].taxName }} : {{currency}} {{ fixDigits(items.totalTax2) }}
							</td>
							<td v-if="taxNames[2].show == true">
								Total {{ taxNames[2].taxName }} : {{currency}} {{ fixDigits(items.totalTax3) }}
							</td>
							<td>Total Tax : {{currency}} {{ fixDigits(items.totalTax) }}</td>
							<td>Net Total : {{currency}} {{ fixDigits(items.totalBill) }}</td>
							<!-- <td>Balance : {{ fixDigits(totalBalance) }}</td> -->
						</tr>
					</table>
				</div>
			</div>
			<template #footer>
				<div class="dataTable-header p-grid p-m-0">
					<div class="p-col p-p-0">
						<MultiSelect :modelValue="selectedColumns" :options="optionalListOptionsTaxes" optionLabel="header" @update:modelValue="onToggle" placeholder="Choose Columns" style="width: 20em"/>
					</div>
					<div class="p-col p-p-0">
						<Button
							type="button"
							label="Print"
							icon="pi pi-print"
							class="p-button-warning pull-left"
							@click="printReceipt()"
						/>
						</div>
				</div>
				</template>
	</Dialog>
</template>

<script lang="ts">
import moment from "moment";
import { Options, Vue } from "vue-class-component";
import Toaster from "../helpers/Toaster";
import PosService from "../service/PosService.js";
import { useStore, ActionTypes } from "../store";


 interface itemList{
	 batchNo: string;
	 brandName: string;
	 categoryName: string;
	 expiryDate: string;
	 freeUnit: number;
	 genericName: string;
	 itemDescription: string;
	 itemDisc: number;
	 itemName: string;
	 mode: string;
	 mrp: number;
	 packSize: number;
	 posReceiptId: number;
	 productType: string;
	 purchaseDisc: number;
	 purchasePrice: number;
	 sectorName: string;
	 sellingPrice: number;
	 sheetSize: number;
	 stockId: number;
	 subTotal: number;
	 supplierBonus: number;
	 tax1: number;
	 tax2: number;
	 tax3: number;
	 totalUnit: number;
	 unit: number;
 }


@Options({
	props: {
		PreviewReceipt: Object,
	},
	watch: {
		PreviewReceipt(obj) {
			
			this.openDialog();

			this.productDialog = obj.status;
		 
			if(obj.receiptID != 0)
			{
				this.loadReceipt(obj.receiptID);
			}
		},
	},
	emits: ["updatePreviewStatus"],
})


export default class PosPreviewReceipt extends Vue {

	private store = useStore();
	private toast;
	private productDialog = false;
	private transferStoreName = "";
	private showOnly = "Both";
	private posService;
	private selectedColumns =  [{header: 'Mode'}];

	private optionalListOptions = [
			{header: 'Mode'},
			{header: 'Batch NO'},
			{header: 'Brand Name'},
			{header: 'Brand Sector'},
			{header: 'Category'},
			{header: 'Product Type'},
			{header: 'Pack Size'},
			{header: 'Strip Size'},
			{header: 'Cus Disc'},
	];
	
	private optionalListOptionsTaxes = [
			{header: 'Mode'},
			{header: 'Batch NO'},
			{header: 'Brand Name'},
			{header: 'Brand Sector'},
			{header: 'Category'},
			{header: 'Product Type'},
			{header: 'Pack Size'},
			{header: 'Strip Size'},
			{header: 'Cus Disc'},
	];

	private itemList: itemList[] = [];

	private items = {
		storeName: "",
		storeAddress: "",
		storeEmail: "",
		storePhone: "",
		storeLicense: "",
		type: "",
		description: "",
		selectedProfile: "",
		billNo: "",
		discount: 0,
		doctorDetails: "",
		patientDetails: "",
		paymentMethod: "",
		receiptDate: "",
		createdDate: "",
		receiptNo: "",
		returnReceipt: "",
		status: "",
		totalBill:0,
		totalChange:0,
		totalGrossAmt:0,
		totalPaid:0,
		totalTax:0,
		totalTax1:0,
		totalTax2:0,
		totalTax3:0,
		totalTendered:0,
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

	//DEFAULT METHOD OF TYPE SCRIPT
	//CALLING WHENEVER COMPONENT LOADS
	created() {
		this.toast = new Toaster();
		this.posService = new PosService();
	}

	mounted()
	{
		const localList =  localStorage.getItem('optionalList');
		
		if(localList != null)
		{
			const parsedList = JSON.parse(localList);
			this.selectedColumns = parsedList;
		}
	}

	//OPEN DIALOG TO ADD NEW ITEM
	openDialog() {
		this.productDialog = true;
	}

	closeDialog() {
		this.$emit("updatePreviewStatus", {});
		this.productDialog = false;
	}

	getCompanyURL() 
	{
		return  require('@/assets/images/logo.png').default;
	}

	loadReceipt(receiptID)
	{
		this.posService.getReceiptData(receiptID).then((res) => {
			if (res != null) {

				if(res.tStoreDetails != null)
				{
					this.transferStoreName = res.tStoreDetails.transfer_branch.name;
				}
		 
				this.items.storeName         = res.storeDetail.name;
				this.items.storeAddress      = res.storeDetail.address;
				this.items.storeEmail        = res.storeDetail.email;
				this.items.storePhone        = res.storeDetail.contact;
				this.items.storeLicense      = res.storeDetail.license_no;
				this.items.description       = res.receipt.description;
				this.items.type              = res.receipt.type;
				this.items.selectedProfile   = res.receipt.profile_name.profileName;

				this.items.billNo             =  res.receipt.bill_no;
				this.items.discount           =  Number(res.receipt.discount);
				this.items.doctorDetails      =  res.receipt.doctor_details;
				this.items.patientDetails     =  res.receipt.patient_details;
				this.items.paymentMethod      =  res.receipt.payment_method;
				this.items.receiptDate        =  res.receipt.receipt_date;
				this.items.createdDate        =  res.receipt.created_at;
				this.items.receiptNo          =  res.receipt.receipt_no;
				this.items.returnReceipt      =  res.receipt.return_receipt;
				this.items.status             =  res.receipt.status;
				this.items.totalBill          =  Number(res.receipt.total_bill);
				this.items.totalChange        =  Number(res.receipt.total_change);
				this.items.totalGrossAmt      =  Number(res.receipt.total_gross_amt);
				this.items.totalPaid          =  Number(res.receipt.total_paid);
				this.items.totalTax           =  Number(res.receipt.total_tax);
				this.items.totalTax1          =  Number(res.receipt.total_tax1);
				this.items.totalTax2          =  Number(res.receipt.total_tax2);
				this.items.totalTax3          =  Number(res.receipt.total_tax3);
				this.items.totalTendered      =  Number(res.receipt.total_tendered);

				let vList = res.receiptList;
			 
				if(vList.length > 0)
				{
					this.itemList = [];

					vList.map(v => {
					 
						this.itemList.push({
							batchNo: v.batch_no,
							brandName: v.brand_name,
							categoryName: v.category_name,
							expiryDate: v.expiry_date,
							freeUnit: Number(v.free_unit),
							genericName: v.generic_name,
							itemDescription: v.item_description,
							itemDisc: Number(v.item_disc),
							itemName: v.item_name,
							mode: v.mode,
							mrp: Number(v.mrp),
							packSize: Number(v.pack_size),
							posReceiptId: Number(v.pos_receipt_id),
							productType: v.product_type,
							purchaseDisc: Number(v.purchase_disc),
							purchasePrice: Number(v.purchase_price),
							sectorName: v.sector_name,
							sellingPrice: Number(v.selling_price),
							sheetSize: Number(v.sheet_size),
							stockId: Number(v.stock_id),
							subTotal: Number(v.sub_total),
							supplierBonus: Number(v.supplier_bonus),
							tax1: Number(v.tax_1),
							tax2: Number(v.tax_2),
							tax3: Number(v.tax_3),
							totalUnit: Number(v.total_unit),
							unit: Number(v.unit),
					});
				});

				}
				
					//taxNames
					this.taxNames = [];

					this.taxNames.push({
						taxName: res.storeDetail.tax_name_1,
						show: res.storeDetail.show_1 == 'true' ? true : false,
						optionalReq: res.storeDetail.required_optional_1,
						taxValue:
							res.storeDetail.show_1 == "true"
								? Number(res.storeDetail.tax_value_1)
								: 0,
						accountHead: res.storeDetail.tax_name1.chartName,
						accountID: res.storeDetail.link1,
					});

					this.taxNames.push({
						taxName: res.storeDetail.tax_name_2,
						show: res.storeDetail.show_2  == 'true' ? true : false,
						optionalReq: res.storeDetail.required_optional_2,
						taxValue:
							res.storeDetail.show_2 == "true"
								? Number(res.storeDetail.tax_value_2)
								: 0,
						accountHead: res.storeDetail.tax_name2.chartName,
						accountID: res.storeDetail.link2,
					});

					this.taxNames.push({
						taxName: res.storeDetail.tax_name_3,
						show: res.storeDetail.show_3  == 'true' ? true : false,
						optionalReq: res.storeDetail.required_optional_3,
						taxValue:
							res.storeDetail.show_3 == "true"
								? Number(res.storeDetail.tax_value_3)
								: 0,
						accountHead: res.storeDetail.tax_name3.chartName,
						accountID: res.storeDetail.link3,
					});

					//CLEAR LIST AND RELOAD
					this.optionalListOptionsTaxes = [];
					this.optionalListOptionsTaxes = this.optionalListOptions;


					if(this.taxNames[0].show)
					{
						
						this.optionalListOptionsTaxes.push({header: this.taxNames[0].taxName});
					}

					if(this.taxNames[1].show)
					{
						this.optionalListOptionsTaxes.push({header: this.taxNames[1].taxName});
					}

					if(this.taxNames[2].show)
					{
						this.optionalListOptionsTaxes.push({header: this.taxNames[2].taxName});
					}
				}
		});
	}


	fixDigits(amt) {
		return Number(amt).toFixed(2);
	}

	formatDate(date)
	{
		return moment(date).format("DD-MM-YYYY");
	}
	
	expiryDate(date)
	{
		return moment(date).format("MMM-YYYY");
	}
	
	formatTime(date)
	{
		return moment(date).format("hh:mm A");
	}

	printReceipt() {
		window.print();
	}

	get receiptTypeName()
	{
		let title = '';

		if(this.items.type == 'INE')
		{
			title = 'Invoice Receipt';
		}
		else if(this.items.type == 'TRN')
		{
			title = 'Transfer Stock';
		}
		else if(this.items.type == 'PUR')
		{
			title = 'Purchase Receipt';
		}
		else if(this.items.type == 'RPU')
		{
			title = 'Purchase Return';
		}
		else if(this.items.type == 'RFD')
		{
			title = 'Return Receipt';
		}
		else
		{
			title = 'INVALID';
		}

		return title;
	}

	get receiptDialogName()
	{
		let title = '';

		if(this.items.type == 'INE')
		{
			title = 'Invoice';
		}
		else if(this.items.type == 'TRN')
		{
			title = 'Preview Transfer Stock';
		}
		else if(this.items.type == 'PUR')
		{
			title = 'Preview Purchase Receipt';
		}
		else if(this.items.type == 'RPU')
		{
			title = 'Preview Purchase Return';
		}
		else if(this.items.type == 'RFD')
		{
			title = 'Preview Return Receipt';
		}
		else
		{
			title = 'INVALID';
		}
		
		return title;
	}

	onToggle(value) {
			this.selectedColumns = this.optionalListOptionsTaxes.filter(col => value.includes(col));
			localStorage.setItem('optionalList',JSON.stringify(this.selectedColumns));
	}

	checkOptionalCol(name: string)
	{
		const res = this.selectedColumns.filter(e => e.header == name);
		return res.length > 0 ? true : false;
	}

	get currency() {
		return this.store.getters.getCurrency;
	}
}
</script>

<style scoped>
.company-logo
{
	width: 120px;
	height: auto;
}

.total-lables {
	background-color: #28a745;
	color: #fff;
	font-size: 14px;
	font-weight: bold;
	margin: 0;
}

.total-lables td{
	padding: 0.15rem;
}

@media print{
.dataTable-header
	{
		display:none;
	}
}
</style>