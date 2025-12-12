<!-- THIS IS SOUMIK CODE - 28-11-2025 -->
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
                            icon="pi pi-filter"
                            label="Filter"
                            class="p-button-primary"
                            @click="openDialog"
                        />
                    </div>
                    <div class="soumik">
                        <Button
                            icon="pi pi-file-excel"
                            label="Export"
                            class="p-button-success"
                            @click="dt.exportCSV()"
                        />
                    </div>
                </template>
            </Toolbar>
            
            <!-- Filter Summary Card -->
            <div class="filter-summary-card">
                <div class="summary-content">
                    <div class="summary-item">
                        <i class="pi pi-tag"></i>
                        <span><strong>Category:</strong> {{ getCurrentCategory }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="pi pi-calendar"></i>
                        <span><strong>Period:</strong> {{ getCurrentPeriod }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="pi pi-chart-line"></i>
                        <span><strong>Total Items:</strong> {{ rList.length }}</span>
                    </div>
                </div>
            </div>

            <div class="p-mt-2">
                <DataTable
                    :value="rList"
                    :paginator="true"
                    :rows="20"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                    :resizableColumns="true"
                    :loading="loading"
                    responsiveLayout="scroll"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} items"
                    ref="dt"
                >
                    <template #empty>
                        <div class="p-text-center p-p-3">
                            <i class="pi pi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p style="margin-top: 1rem; color: #666;">No records found</p>
                        </div>
                    </template>
                    <Column field="productName" header="Product Name" :sortable="true" style="min-width: 200px;"></Column>
                    <Column field="category" header="Category" :sortable="true" style="min-width: 120px;"></Column>
                    <Column field="currentStock" header="Current Stock" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="stock-badge">{{ slotProps.data.currentStock }}</span>
                        </template>
                    </Column>
                    <Column field="purchasePrice" header="Purchase Price" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="price-text">₹ {{ formatAmount(slotProps.data.purchasePrice) }}</span>
                        </template>
                    </Column>
                    <Column field="salePrice" header="Sale Price" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="price-text">₹ {{ formatAmount(slotProps.data.salePrice) }}</span>
                        </template>
                    </Column>
                    <Column field="potentialValue" header="Potential Value" :sortable="true" style="min-width: 140px;">
                        <template #body="slotProps">
                            <span class="amount-text">₹ {{ formatAmount(slotProps.data.potentialValue) }}</span>
                        </template>
                    </Column>
                    <Column field="lastSaleDate" header="Last Sale Date" :sortable="true" style="min-width: 120px;"></Column>
                    <Column field="status" header="Status" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span :class="getStatusClass(slotProps.data.status)">
                                {{ slotProps.data.status }}
                            </span>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '450px' }"
                position="top"
                class="p-fluid filter-dialog"
                :modal="true"
            >
                <template #header>
                    <div class="dialog-header">
                        <i class="pi pi-filter-fill" style="font-size: 1.5rem; margin-right: 10px;"></i>
                        <h4 style="margin: 0;">{{ dialogTitle }}</h4>
                    </div>
                </template>
                <div class="filter-form">
                    <div class="p-field">
                        <label for="category"><i class="pi pi-tag"></i> Category</label>
                        <Dropdown
                            v-model="searchFilters.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select Category"
                        />
                    </div>
                    <div class="p-field">
                        <label for="period"><i class="pi pi-calendar"></i> Time Period</label>
                        <Dropdown
                            v-model="searchFilters.period"
                            :options="periodOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select Period"
                            @change="onPeriodChange"
                        />
                    </div>
                    <div class="p-field" v-if="showCustomDate">
                        <label for="customMonth"><i class="pi pi-calendar-plus"></i> Select Month</label>
                        <Calendar
                            v-model="searchFilters.customMonth"
                            view="month"
                            dateFormat="MM yy"
                            placeholder="Select Month & Year"
                            :showIcon="true"
                        />
                    </div>
                </div>
                <template #footer>
                    <Button
                        label="Cancel"
                        icon="pi pi-times"
                        class="p-button-text"
                        @click="productDialog = false"
                    />
                    <Button
                        label="Apply Filter"
                        icon="pi pi-check"
                        class="p-button-primary"
                        @click="loadList"
                    />
                </template>
            </Dialog>
        </div>
    </section>
</template>

<script lang="ts">
// THIS IS SOUMIK CODE - 28-11-2025
import { Options, mixins } from "vue-class-component";
import { ref } from "vue";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";

interface IReport {
    productName: string;
    category: string;
    currentStock: number;
    purchasePrice: number;
    salePrice: number;
    potentialValue: number;
    lastSaleDate: string;
    status: string;
}

@Options({
    title: 'Non Moving Items Report',
    components: {},
})
export default class NonMovingItems extends mixins(UtilityOptions) {
    private dt = ref();
    private lists: IReport[] = [];
    private reportService;
    private resultTitle = "";
    private productDialog = false;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "Non Moving Items", to: "non-moving-items" },
    ];

    private searchFilters = {
        category: "all",
        period: "current_month",
        customMonth: null as Date | null,
    };

    private showCustomDate = false;

    private categoryOptions = [
        { label: "All Categories", value: "all" },
        { label: "Medicine", value: "Medicine" },
        { label: "FMCG", value: "FMCG" },
    ];

    private periodOptions = [
        { label: "Current Month", value: "current_month" },
        { label: "Previous Month", value: "previous_month" },
        { label: "Last 2 Months", value: "2_months" },
        { label: "Last 3 Months", value: "3_months" },
        { label: "Last 6 Months", value: "6_months" },
        { label: "Custom Month", value: "custom_month" },
        { label: "All Time", value: "all" },
    ];

    private dialogTitle;
    private submitted = false;

    created() {
        this.reportService = new StoreReports();
    }

    mounted() {
        this.loadList();
    }

    openDialog() {
        this.submitted = false;
        this.dialogTitle = "Filter Report";
        this.productDialog = true;
    }

    onPeriodChange() {
        this.showCustomDate = this.searchFilters.period === 'custom_month';
    }

    loadList() {
        this.loading = true;
        const filters = { ...this.searchFilters };
        
        // Format custom month if selected
        if (filters.period === 'custom_month' && filters.customMonth) {
            const date = new Date(filters.customMonth);
            filters.customMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        }
        
        this.reportService.nonMovingItemsReport(filters).then((res) => {
            const data = this.camelizeKeys(res);
            this.resultTitle = data.resultTitle;
            this.lists = data.record;
            this.loading = false;
        });
        this.productDialog = false;
    }

    getStatusClass(status: string) {
        if (status === "Dead Stock") return "status-badge status-dead";
        if (status === "Very Slow") return "status-badge status-very-slow";
        if (status === "Slow Moving") return "status-badge status-slow";
        return "status-badge status-normal";
    }

    formatAmount(amount: any) {
        const num = parseFloat(amount) || 0;
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    get getCurrentCategory() {
        const cat = this.categoryOptions.find(c => c.value === this.searchFilters.category);
        return cat ? cat.label : 'All Categories';
    }

    get getCurrentPeriod() {
        const per = this.periodOptions.find(p => p.value === this.searchFilters.period);
        return per ? per.label : 'Current Month';
    }

    get rList() {
        const l: IReport[] = [];
        this.lists.forEach((e) => {
            const item = {
                ...e,
                lastSaleDate: e.lastSaleDate === 'Never Sold' ? 'Never Sold' : (e.lastSaleDate ? this.formatDate(e.lastSaleDate) : 'N/A'),
                currentStock: parseFloat(e.currentStock as any) || 0,
                purchasePrice: parseFloat(e.purchasePrice as any) || 0,
                salePrice: parseFloat(e.salePrice as any) || 0,
                potentialValue: parseFloat(e.potentialValue as any) || 0,
            };
            l.push(item);
        });
        return l;
    }
}
</script>

<style scoped>
.filter-summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    margin: 15px 0;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.summary-content {
    display: flex;
    justify-content: space-around;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 14px;
}

.summary-item i {
    font-size: 1.5rem;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-dead {
    background-color: #fee;
    color: #c00;
    border: 1px solid #fcc;
}

.status-very-slow {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-slow {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-normal {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.stock-badge {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

.qty-badge {
    background-color: #f3e5f5;
    color: #7b1fa2;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

.amount-text {
    color: #2e7d32;
    font-weight: 600;
}

.price-text {
    color: #1565c0;
    font-weight: 600;
}

.filter-dialog .dialog-header {
    display: flex;
    align-items: center;
    color: #667eea;
}

.filter-form {
    padding: 20px 0;
}

.filter-form .p-field {
    margin-bottom: 20px;
}

.filter-form label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.filter-form label i {
    color: #667eea;
}
</style>
