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
                    <div class="p-mx-2">
                        <Button
                            icon="pi pi-plus"
                            class="p-button-success"
                            @click="openDialog"
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
                <Column header="Created Date">
                    <template #body="slotProps">
                        {{ formatDateTime(slotProps.data.created_at) }}
                    </template>
                </Column>
                <Column header="Updated Date">
                    <template #body="slotProps">
                        {{ formatDateTime(slotProps.data.updated_at) }}
                    </template>
                </Column>
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
                        {{currency}} {{ formatAmount(slotProps.data.total_bill) }}
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
                <Column :exportable="false" header="Action">
                    <template #body="slotProps">
                        <span class="p-buttonset">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-success"
                                @click="editIem(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-chart-bar"
                                class="p-button-rounded p-button-warning"
                                @click="openTransactionDialog(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-print"
                                class="p-button-rounded p-button-primary"
                                @click="openPreviewDialog(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-money-bill"
                                :disabled="
                                    calculateBalance(
                                        slotProps.data.total_bill,
                                        slotProps.data.receipt_balance
                                    ) == 0
                                "
                                class="p-button-rounded p-button-danger"
                                @click="
                                    openReceivePaymentDialog(slotProps.data)
                                "
                            />
                        </span>
                    </template>
                </Column>
            </DataTable>
            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '100vw' }"
                position="top"
                class="p-fluid p-m-0 p-dialog-maximized"
            >
                <template #header>
                    <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        {{ dialogTitle }}
                    </h5>
                </template>
                <div class="p-grid">
                    <div class="p-col">
                        <div class="p-field">
                            <label for="receiptType">Receipt Type</label>
                            <Dropdown
                                id="receiptType"
                                v-model="item.type"
                                @change="setReceiptType"
                                :options="receiptTypes"
                                optionLabel="key"
                                optionValue="value"
                            />
                        </div>
                    </div>
                    <div class="p-col">
                        <div class="p-field">
                            <label
                                for="selectedProfile"
                                :class="{
                                    'p-error':
                                        v$.selectedProfile.$invalid && submitted
                                }"
                                >Account Holders</label
                            >
                            <AutoComplete
                                :delay="1000"
                                :minLength="3"
                                @item-select="saveProfile($event)"
                                scrollHeight="500px"
                                v-model="v$.selectedProfile.$model"
                                :suggestions="profilerList"
                                placeholder="Search Profile"
                                @complete="searchProfiler($event)"
                                :dropdown="false"
                                autoFocus
                            >
                                <template #item="slotProps">
                                    <div>
                                        TITLE :
                                        <b class="pull-right">
                                            {{
                                                slotProps.item.account_title.toUpperCase()
                                            }}
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
                                    v-for="(error, index) of v$.selectedProfile
                                        .$errors"
                                    :key="index"
                                >
                                    <small class="p-error">{{
                                        error.$message
                                    }}</small>
                                </span>
                            </span>
                            <small
                                v-else-if="
                                    (v$.selectedProfile.$invalid &&
                                        submitted) ||
                                        v$.selectedProfile.$pending.$response
                                "
                                class="p-error"
                                >{{
                                    v$.selectedProfile.required.$message.replace(
                                        "Value",
                                        "Account Holder"
                                    )
                                }}</small
                            >
                        </div>
                    </div>
                    <div class="p-col">
                        <div class="p-field">
                            <label
                                for="receiptDate"
                                :class="{
                                    'p-error':
                                        v$.receiptDate.$invalid && submitted
                                }"
                                >Date</label
                            >
                            <Calendar
                                id="receiptDate"
                                v-model="v$.receiptDate.$model"
                                :class="{
                                    'p-invalid':
                                        v$.receiptDate.$invalid && submitted
                                }"
                                selectionMode="single"
                                dateFormat="dd-mm-yy"
                            />
                            <span v-if="v$.receiptDate.$error && submitted">
                                <span
                                    id="p-error"
                                    v-for="(error, index) of v$.receiptDate
                                        .$errors"
                                    :key="index"
                                >
                                    <small class="p-error">{{
                                        error.$message
                                    }}</small>
                                </span>
                            </span>
                            <small
                                v-else-if="
                                    (v$.receiptDate.$invalid && submitted) ||
                                        v$.receiptDate.$pending.$response
                                "
                                class="p-error"
                                >{{
                                    v$.receiptDate.required.$message.replace(
                                        "Value",
                                        "Date"
                                    )
                                }}</small
                            >
                        </div>
                    </div>
                    <div class="p-col">
                        <div class="p-field" v-if="item.id == 0">
                            <label for="paymentType">Payment Type</label>
                            <Dropdown
                                id="paymentType"
                                v-model="item.paymentType"
                                :options="methodList"
                                optionLabel="cardName"
                            />
                        </div>
                    </div>
                </div>
                <div class="p-grid" v-if="item.type == 'RFD'">
                    <div class="p-col-3">
                        <div class="p-inputgroup">
                            <InputText
                                v-model.trim="receiptNO"
                                placeholder="Search Receipt No"
                            />
                            <Button
                                icon="pi pi-search "
                                class="p-button-danger p-mr-1"
                                @click="searchReceiptNo"
                            />
                        </div>
                    </div>
                </div>
                <div class="p-field">
                    <DataTable
                        :value="state.itemList"
                        class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                        responsiveLayout="scroll"
                    >
                        <Column header="ACCOUNT NAME" style="width: 30%">
                            <template #body="slotProps">
                                <AutoComplete
                                    :delay="1000"
                                    :minLength="3"
                                    @item-select="
                                        saveAccountHead($event, slotProps.data)
                                    "
                                    scrollHeight="500px"
                                    v-model="slotProps.data.accountHead"
                                    :suggestions="accountHeadList"
                                    placeholder="Search Account"
                                    @complete="searchAccountHead($event)"
                                    :dropdown="false"
                                    :class="{
                                        'p-invalid':
                                            this.validateHeadList.includes(
                                                state.itemList.indexOf(
                                                    slotProps.data
                                                )
                                            ) && submitted
                                    }"
                                    class="p-p-1"
                                >
                                    <template #item="slotProps">
                                        <div>
                                            Head Code :
                                            <b class="pull-right">
                                                {{
                                                    slotProps.item.account_code.toUpperCase()
                                                }}
                                            </b>
                                        </div>
                                        <div>
                                            Head Name :
                                            <b class="pull-right">
                                                {{
                                                    slotProps.item.account_name
                                                }}
                                            </b>
                                        </div>
                                        <div>
                                            Nature :
                                            <span class="pull-right">
                                                {{
                                                    slotProps.item
                                                        .account_nature
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            Head Type :
                                            <span class="pull-right">
                                                {{
                                                    slotProps.item.account_type
                                                }}
                                            </span>
                                        </div>
                                    </template>
                                </AutoComplete>
                            </template>
                        </Column>
                        <Column header="QTY" style="width: 10%">
                            <template #body="slotProps">
                                <InputNumber
                                    :useGrouping="false"
                                    v-model="slotProps.data.quantity"
                                    :disabled="slotProps.data.accountID == 0"
                                    class="p-p-1"
                                    :allowEmpty="false"
                                />
                            </template>
                        </Column>
                        <Column :header="'PRICE ('+currency+')'" style="width: 10%">
                            <template #body="slotProps">
                                <InputNumber
                                    mode="decimal"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    :disabled="slotProps.data.accountID == 0"
                                    v-model="slotProps.data.price"
                                    :allowEmpty="false"
                                    class="p-p-1"
                                />
                            </template>
                        </Column>
                        <Column header="DISC (%)" style="width: 10%">
                            <template #body="slotProps">
                                <InputNumber
                                    mode="decimal"
                                    :useGrouping="false"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    v-model="slotProps.data.discount"
                                    suffix="%"
                                    :min="0"
                                    :max="100"
                                    :allowEmpty="false"
                                    class="p-p-1"
                                    :disabled="slotProps.data.accountID == 0"
                                />
                            </template>
                        </Column>
                        <Column
                            :header="taxNames[0].taxName + '(%)'"
                            style="width: 10%"
                            v-if="taxNames[0].show == 'true'"
                        >
                            <template #body="slotProps">
                                <InputNumber
                                    mode="decimal"
                                    :useGrouping="false"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    suffix="%"
                                    :min="0"
                                    :max="100"
                                    :allowEmpty="false"
                                    v-model="slotProps.data.tax1Value"
                                    :disabled="
                                        taxNames[0].optionalReq == 'Required' ||
                                            slotProps.data.accountID == 0
                                    "
                                    class="p-p-1"
                                />
                            </template>
                        </Column>
                        <Column
                            :header="taxNames[1].taxName + '(%)'"
                            style="width: 10%"
                            v-if="taxNames[1].show == 'true'"
                        >
                            <template #body="slotProps">
                                <InputNumber
                                    mode="decimal"
                                    :useGrouping="false"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    suffix="%"
                                    :min="0"
                                    :max="100"
                                    :allowEmpty="false"
                                    v-model="slotProps.data.tax2Value"
                                    :disabled="
                                        taxNames[1].optionalReq == 'Required' ||
                                            slotProps.data.accountID == 0
                                    "
                                    class="p-p-1"
                                />
                            </template>
                        </Column>
                        <Column
                            :header="taxNames[2].taxName + '(%)'"
                            style="width: 10%"
                            v-if="taxNames[2].show == 'true'"
                        >
                            <template #body="slotProps">
                                <InputNumber
                                    mode="decimal"
                                    :useGrouping="false"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    suffix="%"
                                    :min="0"
                                    :max="100"
                                    :allowEmpty="false"
                                    v-model="slotProps.data.tax3Value"
                                    :disabled="
                                        taxNames[3].optionalReq == 'Required' ||
                                            slotProps.data.accountID == 0
                                    "
                                    class="p-p-1"
                                />
                            </template>
                        </Column>
                        <Column header="SUBTOTAL" style="width: 10%">
                            <template #body="slotProps">
                                <InputNumber
                                    :disabled="true"
                                    mode="decimal"
                                    :maxFractionDigits="2"
                                    :minFractionDigits="2"
                                    :allowEmpty="false"
                                    :value="getTheSubtotal(slotProps.data)"
                                    class="p-p-1"
                                />
                            </template>
                        </Column>
                        <Column header="Action" style="width: 5%">
                            <template #body="slotProps">
                                <Button
                                    type="button"
                                    icon="pi pi-times"
                                    class="p-button-danger pull-left"
                                    @click="clearListItem(slotProps.data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </div>
                <div class="p-grid">
                    <div class="p-col-8">
                        <div class="p-field">
                            <label
                                for="description"
                                :class="{
                                    'p-error':
                                        v$.description.$invalid && submitted
                                }"
                                >Description</label
                            >
                            <InputText
                                id="description"
                                v-model="v$.description.$model"
                                :class="{
                                    'p-invalid':
                                        v$.description.$invalid && submitted
                                }"
                            />
                            <span v-if="v$.description.$error && submitted">
                                <span
                                    id="p-error"
                                    v-for="(error, index) of v$.description
                                        .$errors"
                                    :key="index"
                                >
                                    <small class="p-error">{{
                                        error.$message
                                    }}</small>
                                </span>
                            </span>
                            <small
                                v-else-if="
                                    (v$.description.$invalid && submitted) ||
                                        v$.description.$pending.$response
                                "
                                class="p-error"
                                >{{
                                    v$.description.required.$message.replace(
                                        "Value",
                                        "Description"
                                    )
                                }}</small
                            >
                        </div>
                    </div>
                    <div class="p-col-4" v-if="item.id == 0">
                        <div class="p-field">
                            <label for="total_paid"> {{
                                totalAmountStatement
                            }} ({{currency}})  </label>
                            <InputNumber
                                mode="decimal"
                                :minFractionDigits="2"
                                :maxFractionDigits="2"
                                :min="0"
                                :allowEmpty="false"
                                :max="netTotal"
                                id="total_paid"
                                v-model="item.totalAmount"
                            />
                        </div>
                    </div>
                </div>
                <div class="p-grid">
                    <div class="p-col-12">
                        <table class="table table-bordered total-lables">
                            <tr>
                                <td>
                                    Total Gross : {{currency}} {{ formatAmount(totalGross) }}
                                </td>
                                <td>
                                    Total Disc : {{currency}}
                                    {{ formatAmount(totalDiscAmount) }}
                                </td>
                                <td v-if="taxNames[0].show == 'true'">
                                    Total {{ taxNames[0].taxName }} :
                                    {{currency}} {{ formatAmount(totalTax1) }}
                                </td>
                                <td v-if="taxNames[1].show == 'true'">
                                    Total {{ taxNames[1].taxName }} :
                                    {{currency}} {{ formatAmount(totalTax2) }}
                                </td>
                                <td v-if="taxNames[2].show == 'true'">
                                    Total {{ taxNames[2].taxName }} :
                                    {{currency}} {{ formatAmount(totalTax3) }}
                                </td>
                                <td>
                                    Total Tax : {{currency}} {{ formatAmount(totalTax) }}
                                </td>
                                <td>
                                    Net Total : {{currency}} {{ formatAmount(netTotal) }}
                                </td>
                                <td>
                                    Balance : {{currency}} {{ formatAmount(totalBalance) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <template #footer>
                    <div class="p-grid">
                        <div class="p-col-8 p-text-left">
                            <div class="p-col-3">
                                <Button
                                    type="button"
                                    label="Clear All"
                                    icon="pi pi-times"
                                    class="p-button-danger pull-left btn-width"
                                    @click="clearLines()"
                                />
                            </div>
                        </div>
                        <div class="p-col-4 p-text-right">
                            <Button
                                type="button"
                                label="Add New"
                                icon="pi pi-plus-circle"
                                class="p-button-success btn-width"
                                @click="addNewLine()"
                            />
                            <Button
                                type="submit"
                                label="Save"
                                :disabled="
                                    item.profileID == 0 ||
                                        state.itemList.length <= 0 ||
                                        netTotal <= 0
                                "
                                icon="pi pi-check"
                                class="p-button-primary btn-width"
                                @click.prevent="saveItem(!v$.$invalid)"
                            />
                        </div>
                    </div>
                </template>
            </Dialog>
            <Dialog
                v-model:visible="previewTransactionDialog"
                :style="{ width: '100vw' }"
                position="top"
                class="p-fluid p-m-0 p-dialog-maximized"
            >
                <template #header>
                    <h4 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        Receipt Transactions
                    </h4>
                </template>
                <h5>Receipt Payments</h5>
                <DataTable
                    :value="PaymentLists"
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
                        $ {{ formatAmount(slotProps.data.transTotalAmount) }}
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

            <PreviewAccountingReceipt
                :PreviewAccountingReceipt="{
                    status: this.previewImageDialog,
                    dialogTitle: this.dialogTitle,
                    previewHeading: this.previewHeading,
                    receiptID: this.receiptID
                }"
                v-on:updatePreviewStatus="updatePreviewStatus"
            />

            <ProfilePaymentReceipt
                :ProfilePaymentReceipt="{
                    status: this.openReceivePayment,
                    dialogTitle: this.dialogTitle,
                    previewHeading: this.previewHeading,
                    receiptID: this.receiptID
                }"
                v-on:updatePaymentStatus="updatePaymentStatus"
            />
        </div>
    </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import ReceiptService from "../../service/ReceiptService.js";
import ProfilerService from "../../service/ProfilerService.js";
import ChartService from "../../service/ChartService.js";
import { reactive } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, requiredIf, helpers } from "@vuelidate/validators";
import Toaster from "../../helpers/Toaster";
import moment from "moment";
import AutoComplete from "primevue/autocomplete";
import SearchFilter from "../../components/SearchFilter.vue";
import PreviewAccountingReceipt from "../../components/PreviewAccountingReceipt.vue";
import ProfilePaymentReceipt from "../../components/ProfilePaymentReceipt.vue";
import UtilityOptions from "../../mixins/UtilityOptions";
import PaymentService from "../../service/PaymentService";

interface IPaymentMethod {
    bankId: number;
    branchId: number;
    cardCharges: number;
    amount: number;
    cardName: string;
    chargeCustomer: string;
    id: string;
}

@Options({
    title: 'Sales/Refund Receipt',
    components: {
        AutoComplete,
        SearchFilter,
        PreviewAccountingReceipt,
        ProfilePaymentReceipt
    }
})
export default class SalesReceipt extends mixins(UtilityOptions) {
    private lists = [];
    private PaymentLists = [
        {
          createdDate: "",
          receiptNo: "",
          transTotalAmount: 0,
          description: "",
          paymentType: "",
          sourceType: "",
        }
    ];

    private profilerList = [];
    private accountHeadList = [];

    private totalAmountStatement = "Total Amount Received";
    private statement = "";
    private methodList: IPaymentMethod[] = [];
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
    private openReceivePayment = false;
    private previewImageDialog = false;
    private productDialog = false;
    private filterDialog = false;
    private submitted = false;
    private statusDialog = false;
    private checkPagination = true;
    private paymentService;
    private totalRecords = 0;
    private limit = 0;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Initialization", to: "initialization" },
        { label: "Receipts", to: "sales-receipt" }
    ];

    private receiptTypes = [
        { key: "Sales Receipt", value: "SLS" },
        { key: "Refund Receipt", value: "RFR" }
    ];

    private itemFilter = {
        keyword: "",
        filterType: "None",
        storeID: 0,
        date1: "",
        date2: "",
        type: "SLS"
    };

    private taxNames = [
        {
            taxName: "",
            show: false,
            optionalReq: "",
            taxValue: 0,
            accountHead: "",
            accountID: 0
        },
        {
            taxName: "",
            show: false,
            optionalReq: "",
            taxValue: 0,
            accountHead: "",
            accountID: 0
        },
        {
            taxName: "",
            show: false,
            optionalReq: "",
            taxValue: 0,
            accountHead: "",
            accountID: 0
        }
    ];

    private state = reactive({
        description: "",
        receiptDate: "",
        selectedProfile: "",
        itemList: [
            {
                accountID: 0,
                accountHead: "",
                quantity: 1,
                price: 0,
                discount: 0,
                subTotal: 0,
                tax1Value: this.taxNames[0].taxValue,
                tax2Value: this.taxNames[1].taxValue,
                tax3Value: this.taxNames[2].taxValue
            }
        ]
    });

    private validationRules = {
        description: {
            required
        },
        receiptDate: {
            required
        },
        selectedProfile: {
            required
        }
    };

    private v$ = useVuelidate(this.validationRules, this.state);

    private item = {
        id: 0,
        transactionID: 0,
        paymentType: {
            cardCharges: 0,
            chargeCustomer: "",
            cardName: "Cash",
            bankId: 0,
            branchId: 0,
            amount: 0,
            id: ""
        },
        status: "Active",
        receiptDueDate: moment().format("YYYY-MM-DD"),
        profileID: 0,
        type: "SLS",
        totalTax1: 0,
        totalTax2: 0,
        totalTax3: 0,
        totalGross: 0,
        totalDiscount: 0,
        totalTax: 0,
        totalBill: 0,
        totalAmount: 0
    };

    private counterEntry = [
        {
            accountID: 0,
            accountHead: "",
            amount: 0,
            type: "Debit"
        }
    ];

    //CALLING WHEN PAGINATION BUTTON CLICKS
    onPage(event) {
        this.loadList(event.first);
    }

    //DEFAULT METHOD OF TYPE SCRIPT
    created() {
        this.receiptService = new ReceiptService();
        this.profilerService = new ProfilerService();
        this.paymentService = new PaymentService();
        this.chartService = new ChartService();
        this.toast = new Toaster();
    }

    //CALLNING AFTER CONSTRUCTOR GET CALLED
    mounted() {
        this.loadList(0);
        this.loadPaymentMethod();
    }

    //OPEN DIALOG TO ADD NEW ITEM
    openDialog() {
        this.clearItem();
        this.submitted = false;
        this.dialogTitle = "Create Receipt";
        this.productDialog = true;
        this.item.type = "SLS";
    }

    openFilterDialog() {
        this.dialogTitle = "Filter Receipt";
        this.filterDialog = true;
    }

    //CLOSE THE ITEM DAILOG BOX
    hideDialog() {
        this.productDialog = false;
        this.submitted = false;
    }

    //ADD OR UPDATE THE ITEM VIA HTTP
    saveItem(isFormValid) {
        this.submitted = true;
        if (isFormValid && this.validateHeadList.length == 0) {
            this.setAccountingEntries();

            if (this.item.id != 0) {
                this.state.receiptDate = moment(this.state.receiptDate).format(
                    "YYYY-MM-DD"
                );

                this.receiptService
                    .update(this.item, this.state, this.counterEntry)
                    .then(res => {
                        this.loadList(this.goToFirstLink);
                        //SHOW NOTIFICATION
                        this.toast.handleResponse(res);
                    });
            } else {
                this.state.receiptDate = moment(this.state.receiptDate).format(
                    "YYYY-MM-DD"
                );
                this.receiptService
                    .save(this.item, this.state, this.counterEntry)
                    .then(res => {
                        this.goToFirstLink = 0;
                        this.loadList(this.goToFirstLink);
                        //SHOW NOTIFICATION
                        this.toast.handleResponse(res);
                    });
            }

            this.productDialog = false;
            this.clearItem();
        }
    }

    //OPEN DIALOG BOX TO EDIT
    editIem(data) {
        this.submitted = false;
        this.dialogTitle = "Update Receipt";
        this.productDialog = true;

        this.receiptService.getItem(data).then(res => {
            if (res != null) {
                this.item.id = Number(res.receipt[0].id);
                this.item.transactionID = Number(res.receipt[0].transaction_id);
                this.item.status = res.receipt[0].status;
                this.item.profileID = Number(res.receipt[0].profile_id);
                this.item.type = res.receipt[0].type;
                this.item.totalTax1 = Number(res.receipt[0].total_tax1);
                this.item.totalTax2 = Number(res.receipt[0].total_tax2);
                this.item.totalTax3 = Number(res.receipt[0].total_tax3);
                this.item.totalGross = Number(res.receipt[0].gross_total);
                this.item.totalDiscount = Number(res.receipt[0].total_discount);
                this.item.totalTax = Number(res.receipt[0].total_tax);
                this.item.totalBill = Number(res.receipt[0].total_bill);

                const b = this.calculateBalance(
                    res.receipt[0].total_bill,
                    res.receipt[0].receipt_balance
                );
                this.item.totalAmount = b;
                this.state.description = res.receipt[0].description;
                this.state.receiptDate = res.receipt[0].receipt_date;
                this.state.selectedProfile =
                    res.receipt[0].profile_name.profileName;

                let vList = res.subReceipt;

                if (vList.length > 0) {
                    this.state.itemList = [];
                    vList.map(v => {
                        this.state.itemList.push({
                            accountID: Number(v.sub_transaction_id),
                            accountHead: v.chart_name.chartName,
                            quantity: Number(v.qty),
                            price: Number(v.price),
                            discount: Number(v.discount),
                            subTotal: Number(v.sub_total),
                            tax1Value: Number(v.tax1),
                            tax2Value: Number(v.tax2),
                            tax3Value: Number(v.tax3)
                        });
                    });
                }
            }
        });
    }

    //FETCH THE DATA FROM SERVER
    loadList(page) {
        this.receiptService.getItems(this.itemFilter, page).then(data => {
            this.lists = data.records;
            this.totalRecords = data.totalRecords;
            this.limit = data.limit;
            this.statement = data.statement;

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
                accountID: data.storeTaxes[0].link1
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
                accountID: data.storeTaxes[0].link2
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
                accountID: data.storeTaxes[0].link3
            });
        });
    }

    clearItem() {
        this.item.id = 0;
        this.item.profileID = 0;
        this.item.status = "Active";
        this.item.totalAmount = 0;
        this.state.description = "";
        this.state.receiptDate = "";
        this.state.selectedProfile = "";
        this.state.itemList = [];
        this.state.itemList.push({
            accountID: 0,
            accountHead: "",
            quantity: 1,
            price: 0,
            discount: 0,
            subTotal: 0,
            tax1Value: this.taxNames[0].taxValue,
            tax2Value: this.taxNames[1].taxValue,
            tax3Value: this.taxNames[2].taxValue
        });
    }

    loadSearchData() {
        this.submitted = true;
        if (this.itemFilter.keyword) {
            this.goToFirstLink = 0;
            this.loadList(this.goToFirstLink);
        }
    }

    searchProfiler(event) {
        setTimeout(() => {
            this.profilerService
                .searchProfiler(event.query.trim())
                .then(data => {
                    this.profilerList = data.records;
                });
        }, 500);
    }

    saveProfile(event) {
        const profileInfo = event.value;
        this.state.selectedProfile = profileInfo.account_title;
        this.item.profileID = profileInfo.id;
    }

    searchAccountHead(event) {
        setTimeout(() => {
            this.chartService
                .searchAccountHead(event.query.trim())
                .then(data => {
                    this.accountHeadList = data.records;
                });
        }, 500);
    }

    saveAccountHead(event, data) {
        const accountInfo = event.value;
        data.accountHead = accountInfo.account_name;
        data.accountID = accountInfo.id;
    }

    addNewLine() {
        this.state.itemList.push({
            accountID: 0,
            accountHead: "",
            quantity: 1,
            price: 0,
            discount: 0,
            subTotal: 0,
            tax1Value: this.taxNames[0].taxValue,
            tax2Value: this.taxNames[1].taxValue,
            tax3Value: this.taxNames[2].taxValue
        });
    }

    clearLines() {
        this.state.itemList = [];
        this.state.itemList.push({
            accountID: 0,
            accountHead: "",
            quantity: 1,
            price: 0,
            discount: 0,
            subTotal: 0,
            tax1Value: this.taxNames[0].taxValue,
            tax2Value: this.taxNames[1].taxValue,
            tax3Value: this.taxNames[2].taxValue
        });

        this.toast.showSuccess("All Items Deleted Successfully");
    }

    clearListItem(item) {
        this.state.itemList.splice(this.state.itemList.indexOf(item), 1);
        this.toast.showSuccess("Item Deleted Successfully");
    }

    get validateHeadList() {
        let invalidListItems: Number[] = [];

        this.state.itemList.map((v, index) => {
            if (v.accountID == 0) {
                invalidListItems.push(index);
            }
        });

        return invalidListItems;
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

    openPreviewDialog(data) {
        this.previewImageDialog = true;

        if (data.type == "SLS") {
            this.dialogTitle = "Preview Sales Receipt";
            this.previewHeading = "Sales Receipt";
        } else {
            this.dialogTitle = "Preview Refund Receipt";
            this.previewHeading = "Refund Receipt";
        }

        this.receiptID = data.id;
    }

    updatePreviewStatus() {
        this.previewImageDialog = false;
    }

    getTheSubtotal(data) {
        const qty = Number(data.quantity);
        const price = Number(data.price);
        const discount = Number(data.discount);
        const tax1 = Number(data.tax1Value);
        const tax2 = Number(data.tax2Value);
        const tax3 = Number(data.tax3Value);

        const total = qty * price;
        const disAmount = (total / 100) * discount;

        const afterDis = total - disAmount;
        const afterTax = (afterDis / 100) * (tax1 + tax2 + tax3);

        const netTotal = afterDis + afterTax;

        data.subTotal = Number(this.formatAmount(netTotal));

        return Number(this.formatAmount(netTotal));
    }

    getTheTotalAfterDisc(data) {
        const qty = Number(data.quantity);
        const price = Number(data.price);
        const discount = Number(data.discount);

        const total = qty * price;
        const disAmount = (total / 100) * discount;

        const afterDis = total - disAmount;

        return Number(this.formatAmount(afterDis));
    }

    get totalGross() {
        let total = 0;
        this.state.itemList.forEach(e => {
            total = total + e.price * e.quantity;
        });

        return total;
    }

    get totalTax1() {
        let total = 0;
        this.state.itemList.forEach(e => {
            const price = e.price * e.quantity;
            const afterDisc = (price / 100) * e.discount;
            total = total + ((price - afterDisc) / 100) * e.tax1Value;
        });

        return Number(total.toFixed(2));
    }

    get totalTax2() {
        let total = 0;
        this.state.itemList.forEach(e => {
            const price = e.price * e.quantity;
            const afterDisc = (price / 100) * e.discount;
            total = total + ((price - afterDisc) / 100) * e.tax2Value;
        });

        return Number(total.toFixed(2));
    }

    get totalTax3() {
        let total = 0;
        this.state.itemList.forEach(e => {
            const price = e.price * e.quantity;
            const afterDisc = (price / 100) * e.discount;
            total = total + ((price - afterDisc) / 100) * e.tax3Value;
        });

        return Number(total.toFixed(2));
    }

    get totalDiscAmount() {
        let total = 0;
        this.state.itemList.forEach(e => {
            const price = e.price * e.quantity;
            total = total + (price / 100) * e.discount;
        });

        return total;
    }

    get netTotal() {
        return Number(
            (
                this.totalGross -
                this.totalDiscAmount +
                this.totalTax1 +
                this.totalTax2 +
                this.totalTax3
            ).toFixed(2)
        );
    }

    get totalTax() {
        return Number(
            (this.totalTax1 + this.totalTax2 + this.totalTax3).toFixed(2)
        );
    }

    setAccountingEntries() {
        this.item.totalTax1 = this.totalTax1;
        this.item.totalTax2 = this.totalTax2;
        this.item.totalTax3 = this.totalTax3;
        this.item.totalGross = this.totalGross;
        this.item.totalDiscount = this.totalDiscAmount;
        this.item.totalTax = this.totalTax;
        this.item.totalBill = this.netTotal;

        this.counterEntry = [];

        let cashOrBankHeadID = 0;
        let cashOrBankHeadName = "";

        if (this.item.paymentType.cardName == "Cash") {
            cashOrBankHeadID = 2;
            cashOrBankHeadName = "Cash in hand";
        } else {
            cashOrBankHeadID = 8;
            cashOrBankHeadName = "Cash at bank";
        }

        if (this.item.type == "SLS") {
            if (this.totalBalance == 0) {
                this.counterEntry.push({
                    accountID: cashOrBankHeadID,
                    accountHead: cashOrBankHeadName,
                    amount: this.netTotal,
                    type: "Debit"
                });
            } else if (this.totalBalance == this.netTotal) {
                this.counterEntry.push({
                    accountID: 4,
                    accountHead: "Accounts receivable",
                    amount: this.netTotal,
                    type: "Debit"
                });
            } else {
                this.counterEntry.push({
                    accountID: cashOrBankHeadID,
                    accountHead: cashOrBankHeadName,
                    amount: this.item.totalAmount,
                    type: "Debit"
                });

                this.counterEntry.push({
                    accountID: 4,
                    accountHead: "Accounts receivable",
                    amount: this.totalBalance,
                    type: "Debit"
                });
            }

            this.state.itemList.forEach(e => {
                this.counterEntry.push({
                    accountID: e.accountID,
                    accountHead: e.accountHead,
                    amount: this.getTheTotalAfterDisc(e),
                    type: "Credit"
                });
            });

            //ADDING TAXES
            if (this.totalTax1 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[0].accountID,
                    accountHead: this.taxNames[0].accountHead,
                    amount: this.totalTax1,
                    type: "Credit"
                });
            }

            if (this.totalTax2 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[1].accountID,
                    accountHead: this.taxNames[1].accountHead,
                    amount: this.totalTax2,
                    type: "Credit"
                });
            }

            if (this.totalTax3 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[2].accountID,
                    accountHead: this.taxNames[2].accountHead,
                    amount: this.totalTax3,
                    type: "Credit"
                });
            }
        } else {
            this.state.itemList.forEach(e => {
                this.counterEntry.push({
                    accountID: e.accountID,
                    accountHead: e.accountHead,
                    amount: this.getTheTotalAfterDisc(e),
                    type: "Debit"
                });
            });

            //ADDING TAXES
            if (this.totalTax1 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[0].accountID,
                    accountHead: this.taxNames[0].accountHead,
                    amount: this.totalTax1,
                    type: "Debit"
                });
            }

            if (this.totalTax2 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[1].accountID,
                    accountHead: this.taxNames[1].accountHead,
                    amount: this.totalTax2,
                    type: "Debit"
                });
            }

            if (this.totalTax3 != 0) {
                this.counterEntry.push({
                    accountID: this.taxNames[2].accountID,
                    accountHead: this.taxNames[2].accountHead,
                    amount: this.totalTax3,
                    type: "Debit"
                });
            }

            if (this.totalBalance == 0) {
                this.counterEntry.push({
                    accountID: cashOrBankHeadID,
                    accountHead: cashOrBankHeadName,
                    amount: this.netTotal,
                    type: "Credit"
                });
            } else if (this.totalBalance == this.netTotal) {
                this.counterEntry.push({
                    accountID: 5,
                    accountHead: "Accounts payable",
                    amount: this.netTotal,
                    type: "Credit"
                });
            } else {
                this.counterEntry.push({
                    accountID: cashOrBankHeadID,
                    accountHead: cashOrBankHeadName,
                    amount: this.item.totalAmount,
                    type: "Credit"
                });

                this.counterEntry.push({
                    accountID: 5,
                    accountHead: "Accounts payable",
                    amount: this.totalBalance,
                    type: "Credit"
                });
            }
        }
    }

    setReceiptType() {
        if (this.item.type == "SLS") {
            this.totalAmountStatement = "Total Amount Received";
        } else {
            this.totalAmountStatement = "Total Amount Paid";
        }
    }

    get totalBalance() {
        let total = this.netTotal - this.item.totalAmount;
        return total;
    }

    calculateBalance(totalBill, receiptBalance) {
        let totalAmount = 0;

        receiptBalance.forEach(e => {
            totalAmount = totalAmount + Number(e.trans_total_amount);
        });

        return Number(totalBill - totalAmount);
    }

     openTransactionDialog(data) {
        this.previewTransactionDialog = true;
        this.PaymentLists = [];

        data.receipt_balance.forEach(e => {
          this.PaymentLists.push({
            createdDate: e.created_at,
            receiptNo: e.receipt_no,
            transTotalAmount: e.trans_total_amount,
            description: e.description,
            sourceType: e.trans_type,
            paymentType: e.payment_type,
          });
        });
    }

    searchReceiptNo() {
        if (this.receiptNO != "") {
            this.receiptService.getReceiptNO(this.receiptNO).then(res => {
                if (res != null) {
                    this.item.profileID = Number(res.receipt.profile_id);
                    this.state.selectedProfile =
                        res.receipt.profile_name.profileName;

                    let vList = res.subReceipt;

                    if (vList.length > 0) {
                        this.state.itemList = [];
                        vList.map(v => {
                            this.state.itemList.push({
                                accountID: Number(v.sub_transaction_id),
                                accountHead: v.chart_name.chartName,
                                quantity: Number(v.qty),
                                price: Number(v.price),
                                discount: Number(v.discount),
                                subTotal: Number(v.sub_total),
                                tax1Value: Number(v.tax1),
                                tax2Value: Number(v.tax2),
                                tax3Value: Number(v.tax3)
                            });
                        });
                    }
                }
            });
        }
    }

    openReceivePaymentDialog(data) {
        this.openReceivePayment = true;

        if (data.type == "SLS") {
            this.dialogTitle = "Receive Sales Payment";
            this.previewHeading = "Payment Receipt";
        } else {
            this.dialogTitle = "Pay Refund Payment";
            this.previewHeading = "Payment Receipt";
        }

        this.receiptID = data.id;
    }

    updatePaymentStatus() {
        this.openReceivePayment = false;
        this.loadList(this.goToFirstLink);
    }

    loadPaymentMethod() {
        this.paymentService.paymentMethods().then(res => {
            this.methodList = this.camelizeKeys(res.option);

            this.methodList.push({
                cardCharges: 0,
                chargeCustomer: "",
                cardName: "Cash",
                bankId: 0,
                branchId: 0,
                amount: 0,
                id: ""
            });
        });
    }

     get currency() {
        return this.store.getters.getCurrency;
    }
}
</script>

<style scoped>
.st-style {
    background-color: #f9f9f9;
    color: #000;
    font-size: 14px;
    padding: 5px;
    margin: 0;
}

.btn-width {
    width: 200px !important;
}

.total-lables {
    background-color: #28a745;
    color: #fff;
    font-size: 18px;
    font-weight: bold;
}
</style>
