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
                <h5>Stock Alert Report</h5>
                <p>{{ resultTitle }}</p>
            </div>
            <div class="p-mt-2">
                <DataTable
                    :value="supplierList"
                    :paginator="true"
                    :rows="50"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                    :loading="loading"
                    responsiveLayout="scroll"
                    ref="dt"
                >
                    <template #empty>
                        <div class="p-text-center p-p-3">No suppliers with low stock found</div>
                    </template>
                    <Column field="supplierName" header="Supplier Name" :sortable="true"></Column>
                    <Column field="medicineCount" header="Low Stock Medicines" :sortable="true">
                        <template #body="slotProps">
                            <span class="badge-count">{{ slotProps.data.medicineCount }}</span>
                        </template>
                    </Column>
                    <Column header="Action">
                        <template #body="slotProps">
                            <Button
                                label="View Medicines"
                                icon="pi pi-eye"
                                class="p-button-sm p-button-info p-mr-2"
                                @click="viewSupplierMedicines(slotProps.data)"
                            />
                            <Button
                                label="Generate Reorder"
                                icon="pi pi-print"
                                class="p-button-sm p-button-warning"
                                @click="generateSupplierReorder(slotProps.data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '50vw' }"
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
                        <div class="p-field">
                            <label for="productName">Product Name</label>
                            <InputText
                                id="productName"
                                v-model="searchFilters.productName"
                                placeholder="Search by product name..."
                            />
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

            <Dialog
                v-model:visible="medicineDialog"
                :style="{ width: '90vw' }"
                :maximizable="true"
                position="top"
                class="p-fluid"
            >
                <template #header>
                    <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        <i class="pi pi-list" style="font-size: 1.2rem"></i>
                        {{ selectedSupplier }} - Low Stock Medicines
                    </h5>
                </template>
                <DataTable :value="selectedMedicines" class="p-datatable-sm p-datatable-striped">
                    <template #empty>
                        <div class="p-text-center p-p-3">No medicines found</div>
                    </template>
                    <Column field="productName" header="Product Name"></Column>
                    <Column field="batchNo" header="Batch No"></Column>
                    <Column field="stripSize" header="Strip Size"></Column>
                    <Column field="packSize" header="Pack Size"></Column>
                    <Column field="qty" header="Current Qty">
                        <template #body="slotProps">
                            <span :class="getStockClass(slotProps.data)">{{ slotProps.data.qty }}</span>
                        </template>
                    </Column>
                    <Column field="minStock" header="Min Stock Required"></Column>
                    <Column field="expiryDate" header="Expiry Date"></Column>
                </DataTable>
                <template #footer>
                    <Button
                        label="Print All"
                        icon="pi pi-print"
                        class="p-button-info"
                        @click="printSupplierMedicines"
                    />
                    <Button
                        label="Close"
                        icon="pi pi-times"
                        class="p-button-secondary"
                        @click="medicineDialog = false"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="reorderFormDialog"
                :style="{ width: '90vw' }"
                :maximizable="true"
                position="top"
                :modal="true"
            >
                <template #header>
                    <h5 class="p-dialog-titlebar p-dialog-titlebar-icon">
                        <i class="pi pi-shopping-cart" style="font-size: 1.2rem"></i>
                        Reorder Form - {{ selectedSupplier }}
                    </h5>
                </template>
                <div id="reorderPrintArea" class="reorder-print-content">
                    <div class="reorder-header">
                        <h3>REORDER FORM</h3>
                        <p><strong>Supplier:</strong> {{ selectedSupplier }}</p>
                        <p><strong>Date & Time:</strong> {{ currentDateTime }}</p>
                    </div>
                    <table class="reorder-table">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Medicine Name</th>
                                <th>Batch No</th>
                                <th>Expiry Date</th>
                                <th>Current Qty</th>
                                <th>Reorder Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(medicine, index) in reorderMedicines" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>{{ medicine.productName }}</td>
                                <td>{{ medicine.batchNo }}</td>
                                <td>{{ medicine.expiryDate }}</td>
                                <td class="qty-danger">{{ medicine.qty }}</td>
                                <td>
                                    <input 
                                        type="number" 
                                        v-model="medicine.reorderQty" 
                                        class="reorder-input" 
                                        min="0"
                                        placeholder="Enter qty"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <template #footer>
                    <Button
                        label="Print"
                        icon="pi pi-print"
                        class="p-button-info"
                        @click="printReorder"
                    />
                    <Button
                        label="Close"
                        icon="pi pi-times"
                        class="p-button-secondary"
                        @click="closeReorderForm"
                    />
                </template>
            </Dialog>
        </div>
    </section>
</template>
<script lang="ts">
import { Options, mixins } from "vue-class-component";
import { ref } from "vue";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";

interface IReport {
    productName: string;
    packSize: string;
    stripSize: string;
    batchNo: string;
    qty: string;
    expiryDate: string;
    minStock: string;
    supplierName: string;
    reorderQty?: string;
}

@Options({
    title: 'Stock Alert Report',
    components: {},
})
export default class StockAlertReport extends mixins(UtilityOptions) {
    private dt = ref();
    private lists: IReport[] = [];
    private reportService;
    private resultTitle = "";
    private productDialog = false;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "Stock Alert Report", to: "stock-alert-report" },
    ];

    private searchFilters = {
        id: "",
        productName: "",
    };
    private dialogTitle;
    private submitted = false;
    private filterBranch = [];
    private medicineDialog = false;
    private supplierGroups: any = {};
    private selectedSupplier = '';
    private selectedMedicines: IReport[] = [];
    private reorderFormDialog = false;
    private reorderMedicines: IReport[] = [];
    private currentDateTime = '';

    //CALLING WHENEVER COMPONENT LOADS
    created() {
        this.reportService = new StoreReports();
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
        this.reportService.stockAlertReport(this.searchFilters).then((res) => {
            if (res && res.record) {
                this.resultTitle = res.resultTitle || '';
                this.lists = res.record || [];
                this.supplierGroups = res.groupedBySupplier || {};
            } else {
                this.resultTitle = 'No records found';
                this.lists = [];
                this.supplierGroups = {};
            }
            this.loading = false;
        }).catch((error) => {
            console.error('Error loading stock alert report:', error);
            this.resultTitle = 'Error loading data';
            this.lists = [];
            this.supplierGroups = {};
            this.loading = false;
        });
        this.productDialog = false;
    }

    get supplierList() {
        const suppliers: any[] = [];
        
        Object.keys(this.supplierGroups).forEach(supplierName => {
            suppliers.push({
                supplierName: supplierName,
                medicineCount: this.supplierGroups[supplierName].length,
                medicines: this.supplierGroups[supplierName]
            });
        });
        
        return suppliers.sort((a, b) => b.medicineCount - a.medicineCount);
    }

    getRowClass(data: IReport) {
        const qty = parseFloat(data.qty) || 0;
        const minStock = parseFloat(data.minStock) || 0;
        
        if (qty === 0) {
            return 'stock-critical';
        } else if (qty <= minStock / 2) {
            return 'stock-danger';
        } else if (qty <= minStock) {
            return 'stock-warning';
        }
        return '';
    }

    viewSupplierMedicines(supplier: any) {
        this.selectedSupplier = supplier.supplierName;
        this.selectedMedicines = supplier.medicines.map((m: IReport) => {
            return {
                ...m,
                expiryDate: this.formatDate(m.expiryDate)
            };
        });
        this.medicineDialog = true;
    }

    generateSupplierReorder(supplier: any) {
        this.selectedSupplier = supplier.supplierName;
        this.reorderMedicines = supplier.medicines.map((m: IReport) => {
            return {
                ...m,
                expiryDate: this.formatDate(m.expiryDate)
            };
        });
        this.updateDateTime();
        this.reorderFormDialog = true;
    }

    updateDateTime() {
        const now = new Date();
        const date = now.toLocaleDateString('en-GB');
        const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        this.currentDateTime = `${date} ${time}`;
    }

    closeReorderForm() {
        this.reorderFormDialog = false;
        this.reorderMedicines = [];
    }

    printSupplierMedicines() {
        const printWindow = window.open('', '', 'height=600,width=800');
        if (!printWindow) return;
        
        let tableRows = '';
        this.selectedMedicines.forEach(med => {
            tableRows += `<tr>
                <td>${med.productName}</td>
                <td>${med.batchNo}</td>
                <td>${med.qty}</td>
                <td>${med.minStock}</td>
                <td>${med.expiryDate}</td>
            </tr>`;
        });
        
        printWindow.document.write(`
            <html>
            <head>
                <title>${this.selectedSupplier} - Low Stock Report</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h2 { text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #4CAF50; color: white; }
                </style>
            </head>
            <body>
                <h2>${this.selectedSupplier}</h2>
                <h3>Low Stock Medicines Report</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Batch No</th>
                            <th>Current Qty</th>
                            <th>Min Stock</th>
                            <th>Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    printReorder() {
        const printWindow = window.open('', '', 'height=600,width=800');
        if (!printWindow) return;
        
        let tableRows = '';
        this.reorderMedicines.forEach((med, index) => {
            tableRows += `<tr>
                <td>${index + 1}</td>
                <td>${med.productName}</td>
                <td>${med.batchNo}</td>
                <td>${med.expiryDate}</td>
                <td style="color: #ff0000; font-weight: bold;">${med.qty}</td>
                <td style="text-align: center; font-weight: bold;">${med.reorderQty || ''}</td>
            </tr>`;
        });
        
        printWindow.document.write(`
            <html>
            <head>
                <title>Reorder Form - ${this.selectedSupplier}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 30px; }
                    .reorder-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
                    .reorder-header h3 { margin: 0; font-size: 24px; }
                    .reorder-header p { margin: 5px 0; }
                    .reorder-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    .reorder-table th, .reorder-table td { border: 1px solid #333; padding: 12px; text-align: left; }
                    .reorder-table th { background-color: #f0f0f0; font-weight: bold; }
                    @media print {
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="reorder-header">
                    <h3>REORDER FORM</h3>
                    <p><strong>Supplier:</strong> ${this.selectedSupplier}</p>
                    <p><strong>Date & Time:</strong> ${this.currentDateTime}</p>
                </div>
                <table class="reorder-table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Medicine Name</th>
                            <th>Batch No</th>
                            <th>Expiry Date</th>
                            <th>Current Qty</th>
                            <th>Reorder Qty</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    calculateReorderQty(product: IReport) {
        const qty = parseFloat(product.qty) || 0;
        const minStock = parseFloat(product.minStock) || 0;
        const reorderQty = Math.max(0, minStock - qty + minStock * 0.2);
        return Math.ceil(reorderQty);
    }

    getStockClass(product: IReport) {
        const qty = parseFloat(product.qty) || 0;
        if (qty === 0) return 'text-danger font-weight-bold';
        return '';
    }


}
</script>

<style scoped>
::v-deep(.stock-critical) {
    background-color: #ff4444 !important;
    color: white !important;
    font-weight: bold;
}

::v-deep(.stock-danger) {
    background-color: #ff6b6b !important;
    color: white !important;
}

::v-deep(.stock-warning) {
    background-color: #fff3cd !important;
    color: #856404 !important;
}

::v-deep(.stock-critical:hover),
::v-deep(.stock-danger:hover),
::v-deep(.stock-warning:hover) {
    opacity: 0.9;
}

.reorder-content {
    max-height: 60vh;
    overflow-y: auto;
}

.supplier-section {
    margin-bottom: 1rem;
}

.text-primary {
    color: #007bff;
    font-weight: bold;
}

.text-danger {
    color: #dc3545;
}

.font-weight-bold {
    font-weight: bold;
}

.reorder-qty-blank {
    min-height: 30px;
    border-bottom: 1px solid #dee2e6;
    width: 100%;
}

.print-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #333;
}

.shop-name {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
    color: #333;
}

.print-date {
    font-size: 14px;
    margin: 5px 0;
    color: #666;
}

.badge-count {
    background-color: #ff6b6b;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
}

.reorder-print-content {
    padding: 20px;
}

.reorder-header {
    text-align: center;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
    margin-bottom: 30px;
}

.reorder-header h3 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

.reorder-header p {
    margin: 5px 0;
    color: #666;
}

.reorder-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.reorder-table th,
.reorder-table td {
    border: 1px solid #333;
    padding: 12px;
    text-align: left;
}

.reorder-table th {
    background-color: #f0f0f0;
    font-weight: bold;
}

.qty-danger {
    color: #ff0000;
    font-weight: bold;
}

.blank-cell {
    min-height: 40px;
    border-bottom: 2px solid #999;
}

.reorder-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}

.reorder-input:focus {
    outline: none;
    border-color: #667eea;
}



@media print {
    .print-header {
        display: block !important;
    }
}
</style>
