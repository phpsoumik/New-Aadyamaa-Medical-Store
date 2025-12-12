<template>
    <section>
        <div class="app-container">
            <Toolbar>
                <template #start>
                    <Breadcrumb
                        :home="home"
                        :model="items"
                        class="p-menuitem-text"
                    />
                </template>

                <template #end>
                    <div class="p-mx-2">
                        <Dropdown
                            v-model="itemFilter.type"
                            :options="receiptTypes"
                            optionLabel="key"
                            optionValue="value"
                            @change="loadList(0)"
                        />
                    </div>
                    <div class="p-inputgroup">
                        <InputText
                            v-model.trim="itemFilter.keyword"
                            placeholder="Receipt No"
                        />
                        <Button
                            icon="pi pi-search "
                            class="p-button-primary p-mr-1"
                            @click="loadSearchData"
                        />
                    </div>
                    <div class="p-mx-2">
                        <Button
                            icon="pi pi-calendar"
                            class="p-button-warning"
                            @click="openFilterDialog"
                        />
                    </div>
                </template>
            </Toolbar>
            <p class="st-style p-text-center">
                <b>{{ statement }}</b>
            </p>
            <DataTable
                v-model:first.sync="goToFirstLink"
                :value="lists"
                :lazy="true"
                :paginator="checkPagination"
                :rows="limit"
                :totalRecords="totalRecords"
                :scrollable="true"
                @page="onPage($event)"
                class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
            >
                <template #empty>
                    <div class="p-text-center p-p-3">No records found</div>
                </template>
                <Column header="Receipt Date">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.receipt_date) }}
                    </template>
                </Column>
                <Column field="receipt_no" header="Receipt No"></Column>
                <Column header="Account Title">
                    <template #body="slotProps">
                        {{ slotProps.data.profile_name.profileName }}
                    </template>
                </Column>
                <Column header="Total Amount">
                    <template #body="slotProps">
                        {{currency}} {{ slotProps.data.total_bill }}
                    </template>
                </Column>
                <Column header="Balance">
                    <template #body="slotProps">
                         {{currency}} 
                        {{
                            formatAmount(
                                calculateBalance(
                                    slotProps.data.total_bill,
                                    slotProps.data.receipt_balance
                                )
                            )
                        }}
                    </template>
                </Column>
                <Column field="description" header="Description"></Column>
                <Column field="user_name.userName" header="Created By"></Column>
                <Column header="Store Name">
                    <template #body="slotProps">
                        {{ slotProps.data.branch.branchName }} (
                        {{ slotProps.data.branch.branchCode }} )
                    </template>
                </Column>
                <Column field="status" header="Status"></Column>
                <Column :exportable="false" header="Action">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-cog"
                            class="p-button-primary"
                            @click="openMenu(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>
            <Sidebar
                v-model:visible="menuBar"
                position="bottom"
                class="p-sidebar-sm"
            >
                <h4>Please choose any of the action below :</h4>
                <div class="p-grid">
                    <div class="p-col">
                        <Button
                            icon="pi pi-print"
                            label="VIEW/MODIFY"
                            @click="previewDialog()"
                            class="p-button-primary p-button-md p-mx-2"
                        />
                        <Button
                            icon="pi pi-print"
                            label="PRINT RECEIPT"
                            @click="previewDialog()"
                            class="p-button-primary p-button-md p-mx-2"
                        />
                        <Button
                            icon="pi pi-times"
                            @click="voidReceipt()"
                           
                            label="VOID RECEIPT"
                            class="p-button-danger p-button-md p-mx-2"
                        />
                        <!-- v-if="
                                itemFilter.type != 'ASR' &&
                                    item.status == 'Active' &&
                                    selectedReceiptData.paymentTransactions
                                        .length == 0
                            " -->
                        <Button
                            icon="pi pi-money-bill"
                            v-if="
                                itemFilter.type != 'ASR' &&
                                    totalBalanceReceipt > 0.02
                            "
                            label="PAY/RECEIVE PAYMENT"
                            @click="openPaymentScreen()"
                            class="p-button-success p-button-md p-mx-2"
                        />
                        <Button
                            icon="pi pi-chart-bar"
                            @click="openTransactionDialog()"
                            label="PAYMENT HISTORY"
                            class="p-button-warning p-button-md p-mx-2"
                        />
                        <Button
                            @click="stockLeft()"
                            v-if="item.type == 'TRN' && item.status == 'Active'"
                            icon="pi pi-shopping-cart"
                            label="LEAVE STOCK"
                            class="p-button-md p-mx-2"
                        />
                        <Button
                            @click="saveStock()"
                            v-if="
                                itemFilter.type == 'ASR' &&
                                    item.status == 'Stock Left'
                            "
                            icon="pi pi-check-circle"
                            label="SAVE STOCK"
                            class="p-button-md p-mx-2"
                        />
                    </div>
                </div>
            </Sidebar>
            <Dialog
                v-model:visible="previewTransactionDialog"
                :style="{ width: '100vw' }"
                position="top"
                class="p-fluid p-m-0 p-dialog-maximized"
            >
                <template #header>
                    <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        Receipt Payments
                    </h4>
                </template>
                <DataTable
                    :value="selectedReceiptData.paymentTransactions"
                    :scrollable="true"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                >
                    <template #empty>
                        <div class="p-text-center p-p-3">No records found</div>
                    </template>
                    <Column header="Created Date">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.createdDate) }}
                        </template>
                    </Column>
                    <Column field="sourceType" header="Type"></Column>
                    <Column header="Receipt No">
                        <template #body="slotProps">
                            {{ slotProps.data.receiptNo }}
                        </template>
                    </Column>
                    <Column field="paymentType" header="Payment Type"></Column>
                    <Column header="Total Amount">
                        <template #body="slotProps">
                             {{currency}}
                            {{ formatAmount(slotProps.data.transTotalAmount) }}
                        </template>
                    </Column>
                    <Column field="description" header="Description"></Column>
                </DataTable>
            </Dialog>
            <SearchFilter
                :searchDetail="{
                    status: this.filterDialog,
                    dialogTitle: this.dialogTitle
                }"
                v-on:updateFilterStatus="updateFilterStatus"
            />

            <PosPreviewReceipt
                :PreviewReceipt="{
                    status: this.previewPosReceipt,
                    dialogTitle: this.dialogTitle,
                    previewHeading: this.previewHeading,
                    receiptID: this.receiptID
                }"
                v-on:updatePreviewStatus="updatePreviewStatus"
            />

            <PaymentScreen
                :receiptDetail="{
                    dialogStatus: paymentDialog,
                    itemSource: item.type,
                    restriction: 'No',
                    dialogTilte: dialogTitle,
                    customerID: this.item.profileID,
                    customerName: this.item.selectedProfile,
                    closeConfirmation: true
                }"
                v-on:closePaymentScreenEvent="closePaymentScreen"
                v-on:getProceededPaymentsEvent="getProceededPayments"
            />
        </div>
    </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import PosService from "../../service/PosService.js";
import ProfilerService from "../../service/ProfilerService.js";
import ChartService from "../../service/ChartService.js";
import Toaster from "../../helpers/Toaster";
import AutoComplete from "primevue/autocomplete";
import SearchFilter from "../../components/SearchFilter.vue";
import PosPreviewReceipt from "../../components/PosPreviewReceipt.vue";
import PaymentScreen from "../../components/PaymentScreen.vue";
import { ActionTypes } from "../../store";
import UtilityOptions from "../../mixins/UtilityOptions";

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
    bankID: number;
}

@Options({
    title: 'Transactions',
    components: {
        PaymentScreen,
        AutoComplete,
        SearchFilter,
        PosPreviewReceipt
    }
})
export default class TransactionReceipt extends mixins(UtilityOptions) {
    private lists = [];

    private selectedReceiptData = {
        paymentTransactions: []
    };

    private profilerList = [];
    private accountHeadList = [];
    private totalAmountStatement = "Total Amount Received";
    private statement = "";
    private menuBar = false;
    private paymentDialog = false;
    private dialogTitle = "";
    private toast;
    private goToFirstLink = 0;
    private receiptNO = "";
    private previewHeading = "";
    private receiptID = 0;
    private receiptService;
    private profilerService;
    private chartService;
    private previewTransactionDialog = false;
    private previewPosReceipt = false;
    private productDialog = false;
    private filterDialog = false;
    private submitted = false;
    private statusDialog = false;
    private checkPagination = true;
    private totalRecords = 0;
    private totalBalanceReceipt = 0;
    private limit = 0;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [{ label: "Transactions" }];

    private counterEntry = [
        {
            accountID: 0,
            accountHead: "",
            amount: 0,
            type: "Debit"
        }
    ];

    private receiptTypes = [
        { key: "Sales Receipt", value: "INE" },
        { key: "Refund Receipt", value: "RFD" },
        { key: "Transfer Stocks", value: "TRN" },
        { key: "Arrived Stocks", value: "ASR" },
        { key: "Purchase Stocks", value: "PUR" },
        { key: "Purchase Return", value: "RPU" }
    ];

    private itemFilter = {
        keyword: "",
        filterType: "None",
        storeID: 0,
        date1: "",
        date2: "",
        type: "INE"
    };

    private paymentList: PaymentListType[] = [];

    private item = {
        id: 0,
        profileID: 0,
        selectedProfile: "",
        type: "",
        status: ""
    };

    //CALLING WHEN PAGINATION BUTTON CLICKS
    onPage(event) {
        this.loadList(event.first);
    }

    //DEFAULT METHOD OF TYPE SCRIPT
    created() {
        this.receiptService = new PosService();
        this.profilerService = new ProfilerService();
        this.chartService = new ChartService();
        this.toast = new Toaster();
    }

    //CALLNING AFTER CONSTRUCTOR GET CALLED
    mounted() {
        this.loadList(0);
    }

    openFilterDialog() {
        this.dialogTitle = "Filter Receipt";
        this.filterDialog = true;
    }

    //FETCH THE DATA FROM SERVER
    loadList(page) {
        this.receiptService
            .transactionList(this.itemFilter, page)
            .then(data => {
                this.lists = data.records;
                this.totalRecords = data.totalRecords;
                this.limit = data.limit;
                this.statement = data.statement;
            });
    }

    loadSearchData() {
        this.submitted = true;
        if (this.itemFilter.keyword) {
            this.goToFirstLink = 0;
            this.loadList(this.goToFirstLink);
        }
    }

    updateFilterStatus(obj) {
        if (obj != null && obj.loading == "Yes") {
            this.itemFilter.filterType = obj.filterName.value;
            this.itemFilter.storeID = obj.storeID.id;
            this.itemFilter.date1 = obj.date1;
            this.itemFilter.date2 = obj.date2;
            this.itemFilter.keyword = "";
            this.goToFirstLink = 0;
            this.loadList(this.goToFirstLink);
        }
        this.filterDialog = false;
    }

    calculateBalance(totalBill, receiptBalance) {
        let totalAmount = 0;
        receiptBalance.forEach(e => {
            totalAmount = totalAmount + Number(e.trans_total_amount);
        });
        return Number(totalBill - totalAmount);
    }

    openTransactionDialog() {
        this.previewTransactionDialog = true;
        this.menuBar = false;
    }

    openPaymentScreen() {
        this.paymentDialog = true;
        this.menuBar = false;
        this.dialogTitle = "Receive Or Pay Receipt Dues";

        if (Number(this.totalBalanceReceipt) > 0) {
            this.paymentDialog = true;
            this.store.dispatch(
                ActionTypes.TOTAL_BILL,
                Number(this.formatAmount(this.totalBalanceReceipt))
            );
        } else {
            this.toast.showError(
                "Receipt balance is not valid to proceed next"
            );
        }
    }

    openMenu(data) {
        //alert('open menu');
        this.menuBar = true;
        this.selectedReceiptData.paymentTransactions = this.camelizeKeys(
            data.receipt_balance
        );
        this.item.type = data.type;
        this.item.profileID = data.profile_id;
        this.item.id = data.id;
        this.item.status = data.status;
        this.item.selectedProfile = data.profile_name.profileName;
        // this.paymentList = data.payment_transactions;
        this.totalBalanceReceipt = this.calculateBalance(
            data.total_bill,
            data.receipt_balance
        );
    }

    closePaymentScreen() {
        this.paymentDialog = false;
    }

    getProceededPayments(paymentList) {
        this.paymentList = paymentList;

        this.setAccountingEntries();

        this.receiptService
            .savePayment(this.item, this.paymentList, this.counterEntry)
            .then(res => {
                if (res.alert == "info") {
                    this.loadList(this.goToFirstLink);
                    this.clearAll();
                }

                this.toast.handleResponse(res);
            });

        this.paymentDialog = false;
        this.submitted = false;
    }

    stockLeft() {
        this.receiptService.stockLeft(this.item).then(res => {
            if (res.alert == "info") {
                this.loadList(this.goToFirstLink);
                this.clearAll();
            }

            this.toast.handleResponse(res);
        });

        this.menuBar = false;
    }

    saveStock() {
        this.receiptService.saveStock(this.item).then(res => {
            if (res.alert == "info") {
                this.loadList(this.goToFirstLink);
                this.clearAll();
            }

            this.toast.handleResponse(res);
        });

        this.menuBar = false;
    }

    getPaymentMwthod(paymnetList) {
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
    voidReceipt() {
        this.receiptService.voidStock(this.item).then(res => {
            if (res.alert == "info") {
                this.loadList(this.goToFirstLink);
                this.clearAll();
            }

            this.toast.handleResponse(res);
        });

        this.menuBar = false;
    }

    clearAll() {
        this.paymentList = [];

        this.item = {
            id: 0,
            profileID: 0,
            selectedProfile: "",
            type: "",
            status: ""
        };
    }

    updatePreviewStatus() {
        this.previewPosReceipt = false;
    }

    previewDialog() {
       // alert('preview dialog');
        this.receiptID = this.item.id;
        this.previewPosReceipt = true;
        this.menuBar = false;
    }

    get totalPaidCash() {
        let total = 0;

        this.paymentList.forEach(e => {
            if (e.paymentType == "Cash") {
                total = total + e.transTotalAmount;
            }
        });

        return total;
    }

    get totalPaidBank() {
        let total = 0;

        this.paymentList.forEach(e => {
            if (e.paymentType != "Cash") {
                total = total + e.transTotalAmount;
            }
        });

        return total;
    }

    setAccountingEntries() {
        this.counterEntry = [];

        let totalPaid = this.totalPaidCash + this.totalPaidBank;

        if (totalPaid > 0) {
            if (
                this.item.type == "INE" ||
                this.item.type == "TRN" ||
                this.item.type == "RPU"
            ) {
                if (this.totalPaidCash > 0) {
                    this.counterEntry.push({
                        accountID: 2,
                        accountHead: "Cash in hand",
                        amount: this.totalPaidCash,
                        type: "Debit"
                    });
                }

                if (this.totalPaidBank > 0) {
                    this.counterEntry.push({
                        accountID: 8,
                        accountHead: "Cash at bank",
                        amount: this.totalPaidBank,
                        type: "Debit"
                    });
                }

                this.counterEntry.push({
                    accountID: 4,
                    accountHead: "Accounts receivable",
                    amount: totalPaid,
                    type: "Credit"
                });
            } else if (this.item.type == "RFD" || this.item.type == "PUR") {
                this.counterEntry.push({
                    accountID: 5,
                    accountHead: "Accounts payable",
                    amount: totalPaid,
                    type: "Debit"
                });

                if (this.totalPaidCash > 0) {
                    this.counterEntry.push({
                        accountID: 2,
                        accountHead: "Cash in hand",
                        amount: this.totalPaidCash,
                        type: "Credit"
                    });
                }

                if (this.totalPaidBank > 0) {
                    this.counterEntry.push({
                        accountID: 8,
                        accountHead: "Cash at bank",
                        amount: this.totalPaidBank,
                        type: "Credit"
                    });
                }
            } else {
                //TRANSFER ENTRY YET NEEDS TO BE DONE
            }
        }
    }

    get currency() {
        return this.store.getters.getCurrency;
    }
}
</script>
