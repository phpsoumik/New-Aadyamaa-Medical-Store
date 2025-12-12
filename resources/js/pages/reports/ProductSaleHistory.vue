<!-- THIS IS SOUMIK CODE - 28-11-2025 -->
<template>
    <section>
        <div class="app-container">
            <Toolbar>
                <template #start>
                    <Breadcrumb :home="home" :model="items" class="p-menuitem-text p-p-1" />
                </template>
                <template #end>
                    <Button 
                        v-if="selectedProducts.length > 0"
                        icon="pi pi-file-pdf" 
                        :label="`Create Bulk Order (${selectedProducts.length})`" 
                        class="p-button-success" 
                        @click="createBulkOrderVoucher" 
                        style="margin-right: 10px;"
                    />
                    <Button icon="pi pi-file-excel" label="Export" class="p-button-success" @click="dt.exportCSV()" />
                </template>
            </Toolbar>

            <!-- Top Selling Products Section -->
            <div class="top-selling-section">
                <div class="section-header">
                    <div>
                        <i class="pi pi-chart-bar"></i>
                        <h4>Top Selling Products ({{ topSellingProducts.length }} items)</h4>
                    </div>
                    <div class="filter-group">
                        <Dropdown
                            v-model="topSellingFilters.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            @change="loadTopSellingProducts"
                            placeholder="Category"
                            style="width: 150px;"
                        />
                        <Dropdown
                            v-model="topSellingFilters.brand"
                            :options="brandOptions"
                            optionLabel="label"
                            optionValue="value"
                            @change="loadTopSellingProducts"
                            placeholder="Brand"
                            style="width: 150px;"
                        />
                        <Dropdown
                            v-model="topSellingFilters.dateRange"
                            :options="dateRangeOptions.slice(0, 6)"
                            optionLabel="label"
                            optionValue="value"
                            @change="loadTopSellingProducts"
                            placeholder="Date Range"
                            style="width: 180px;"
                        />
                    </div>
                </div>
                <DataTable
                    v-if="topSellingProducts.length > 0"
                    :value="topSellingProducts"
                    :paginator="true"
                    :rows="20"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    class="p-datatable-sm p-datatable-striped p-datatable-gridlines"
                    responsiveLayout="scroll"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products"
                    @row-click="selectProduct($event.data.productName)"
                    :rowHover="true"
                >
                    <Column style="width: 50px;">
                        <template #header>
                            <Checkbox v-model="selectAll" @change="toggleSelectAll" :binary="true" />
                        </template>
                        <template #body="slotProps">
                            <Checkbox 
                                :modelValue="selectedProducts.includes(slotProps.data.productName)" 
                                @update:modelValue="toggleProductSelection(slotProps.data.productName)"
                                :binary="true" 
                                @click.stop
                            />
                        </template>
                    </Column>
                    <Column field="productName" header="Product Name" :sortable="true" style="min-width: 200px;">
                        <template #body="slotProps">
                            <strong>{{ slotProps.data.productName }}</strong>
                        </template>
                    </Column>
                    <Column field="category" header="Category" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="category-tag">{{ slotProps.data.category }}</span>
                        </template>
                    </Column>
                    <Column field="totalQuantitySold" header="Units Sold" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="qty-badge">{{ slotProps.data.totalQuantitySold }}</span>
                        </template>
                    </Column>
                    <Column field="totalRevenue" header="Total Revenue" :sortable="true" style="min-width: 140px;">
                        <template #body="slotProps">
                            <span class="amount-text">₹{{ formatAmount(slotProps.data.totalRevenue) }}</span>
                        </template>
                    </Column>
                    <Column field="avgSalePrice" header="Avg Price" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="price-text">₹{{ formatAmount(slotProps.data.avgSalePrice) }}</span>
                        </template>
                    </Column>
                    <Column field="totalTransactions" header="Transactions" :sortable="true" style="min-width: 120px;"></Column>
                    <Column field="currentStock" header="Current Stock" :sortable="true" style="min-width: 120px;"></Column>
                    <Column field="lastSaleDate" header="Last Sale" :sortable="true" style="min-width: 120px;"></Column>
                    <Column field="salesStatus" header="Status" :sortable="true" style="min-width: 120px;">
                        <template #body="slotProps">
                            <span class="status-badge" :class="getStatusClass(slotProps.data.salesStatus)">{{ slotProps.data.salesStatus }}</span>
                        </template>
                    </Column>
                </DataTable>
                <div v-else class="empty-state">
                    <i class="pi pi-inbox"></i>
                    <p>No top selling products found for the selected period.</p>
                </div>
            </div>

            <!-- Product Details Section -->
            <div v-if="productInfo" class="product-details-section">
                <div class="details-header">
                    <h3><i class="pi pi-box"></i> {{ productInfo.productName }}</h3>
                    <Button label="Create Order Voucher" icon="pi pi-plus" class="p-button-success" @click="createOrderVoucher" />
                </div>
                <div class="details-grid">
                    <div class="detail-item">
                        <label>Brand Name</label>
                        <span>{{ summaryData?.brandName || productInfo.brand }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Strip Size : Pack Size</label>
                        <span>{{ Math.floor(productInfo.currentStock / productInfo.stripSize) }}:{{ productInfo.currentStock % productInfo.stripSize }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Current Stock (Units)</label>
                        <span class="stock-value">{{ productInfo.currentStock }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Recommended Strips</label>
                        <span class="recommend-value">{{ productInfo.recommendedStrips }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Supplier Name</label>
                        <span>{{ productInfo.supplierName }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script lang="ts">
// THIS IS SOUMIK CODE - 28-11-2025
import { Options, mixins } from "vue-class-component";
import { ref } from "vue";
import StoreReports from "../../service/StoreReports";
import UtilityOptions from "../../mixins/UtilityOptions";

interface ISaleHistory {
    receiptDate: string;
    receiptNo: string;
    customerName: string;
    quantity: number;
    salePrice: number;
    totalAmount: number;
    soldBy: string;
}

@Options({
    title: 'Product Sale History',
    components: {},
})
export default class ProductSaleHistory extends mixins(UtilityOptions) {
    private dt = ref();
    private lists: ISaleHistory[] = [];
    private reportService;
    private loading = false;
    private home = { icon: "pi pi-home", to: "/" };
    private items = [
        { label: "Reports", to: "reports" },
        { label: "Product Sale History", to: "product-sale-history" },
    ];

    private searchFilters = {
        productName: "",
        dateRange: "all",
    };

    private topSellingFilters = {
        category: "all",
        brand: "all",
        dateRange: "30_days",
        limit: 30,
    };

    private productSuggestions: string[] = [];
    private summaryData: any = null;
    private productInfo: any = null;
    private topSellingProducts: any[] = [];
    private brandOptions: any[] = [];
    private selectedProducts: string[] = [];
    private selectAll: boolean = false;

    private categoryOptions = [
        { label: "All Categories", value: "all" },
        { label: "FMCG", value: "107" },
        { label: "Medicine", value: "108" },
    ];

    private dateRangeOptions = [
        { label: "Last 7 Days", value: "7_days" },
        { label: "Last 15 Days", value: "15_days" },
        { label: "Last 30 Days", value: "30_days" },
        { label: "Last 3 Months", value: "3_months" },
        { label: "Last 6 Months", value: "6_months" },
        { label: "This Year", value: "this_year" },
        { label: "All Time", value: "all" },
    ];

    created() {
        this.reportService = new StoreReports();
        this.loadBrands();
        this.loadTopSellingProducts();
    }

    loadBrands() {
        this.reportService.getBrands().then((res: any) => {
            const brands = res.brands || [];
            this.brandOptions = [
                { label: "All Brands", value: "all" },
                ...brands.map((b: any) => ({ 
                    label: b.name, 
                    value: b.id 
                }))
            ];
        }).catch((error: any) => {
            console.error('Error loading brands:', error);
        });
    }

    loadTopSellingProducts() {
        console.log('Loading top selling products...', this.topSellingFilters);
        this.reportService.topSellingProducts(this.topSellingFilters).then((res: any) => {
            console.log('Top selling products response:', res);
            const data = this.camelizeKeys(res);
            this.topSellingProducts = (data.record || []).map((item: any) => ({
                ...item,
                category: this.mapCategory(item.category)
            }));
            console.log('Top selling products loaded:', this.topSellingProducts.length);
        }).catch((error: any) => {
            console.error('Error loading top selling products:', error);
            this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load top selling products', life: 3000 });
        });
    }

    mapCategory(category: any) {
        if (category === '107' || category === 'FMCG') return 'FMCG';
        if (category === '108' || category === 'Medicine') return 'Medicine';
        if (category === '13') return 'Medicine';
        return category || 'N/A';
    }

    selectProduct(productName: string) {
        this.searchFilters.productName = productName;
        this.loadHistory();
    }

    getRankClass(index: number) {
        if (index === 0) return 'rank-gold';
        if (index === 1) return 'rank-silver';
        if (index === 2) return 'rank-bronze';
        return 'rank-default';
    }

    createOrderVoucher() {
        if (!this.productInfo || !this.productInfo.supplierName) {
            this.$toast.add({ severity: 'warn', summary: 'Warning', detail: 'Supplier information not available', life: 3000 });
            return;
        }

        // Generate PDF
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Please allow popups', life: 3000 });
            return;
        }

        const currentDate = new Date().toLocaleString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Order Voucher - ${this.productInfo.supplierName}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; padding: 30px; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
                    .header h1 { font-size: 28px; color: #333; margin-bottom: 10px; }
                    .header-info { display: flex; justify-content: space-between; margin-top: 15px; font-size: 14px; }
                    .header-info div { font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #333; padding: 12px; text-align: left; }
                    th { background-color: #f0f0f0; font-weight: bold; }
                    .qty-col { width: 100px; }
                    .footer { margin-top: 50px; text-align: right; }
                    .signature { margin-top: 80px; border-top: 2px solid #333; width: 200px; float: right; padding-top: 10px; text-align: center; }
                    @media print {
                        body { padding: 20px; }
                        button { display: none; }
                    }
                    .print-btn { margin: 20px 0; padding: 10px 20px; background: #667eea; color: white; border: none; cursor: pointer; font-size: 16px; border-radius: 5px; }
                    .print-btn:hover { background: #5568d3; }
                </style>
            </head>
            <body>
                <button class="print-btn" onclick="window.print()">Print Order Voucher</button>
                
                <div class="header">
                    <h1>ORDER VOUCHER</h1>
                    <div class="header-info">
                        <div>Supplier: ${this.productInfo.supplierName}</div>
                        <div>Date: ${currentDate}</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Brand Name</th>
                            <th>Strip Size</th>
                            <th>Pack Size</th>
                            <th class="qty-col">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${this.productInfo.productName}</td>
                            <td>${this.summaryData?.brandName || this.productInfo.brand || 'N/A'}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        ${Array(15).fill(0).map(() => `
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <div class="footer">
                    <div class="signature">
                        Authorized Signature
                    </div>
                </div>
            </body>
            </html>
        `;

        printWindow.document.write(html);
        printWindow.document.close();
        
        this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Order voucher generated', life: 3000 });
    }

    getStatusClass(status: string) {
        const statusMap: any = {
            'Hot Selling': 'status-hot',
            'High Demand': 'status-high',
            'Good Sales': 'status-good',
            'Moderate': 'status-moderate',
            'Normal': 'status-normal'
        };
        return statusMap[status] || 'status-normal';
    }



    loadHistory() {
        if (!this.searchFilters.productName) {
            this.$toast.add({ severity: 'warn', summary: 'Warning', detail: 'Please enter product name', life: 3000 });
            return;
        }

        this.loading = true;
        this.reportService.productSaleHistory(this.searchFilters).then((res: any) => {
            const data = this.camelizeKeys(res);
            this.lists = data.history || [];
            this.summaryData = data.summary;
            this.productInfo = data.productInfo;
            this.loading = false;
            
            if (this.summaryData.isTopSelling) {
                this.$toast.add({ 
                    severity: 'success', 
                    summary: 'Top Selling Product!', 
                    detail: `${this.productInfo.productName} is a ${this.summaryData.salesStatus} product. Consider restocking.`, 
                    life: 5000 
                });
            }
        }).catch(() => {
            this.loading = false;
        });
    }

    formatAmount(amount: any) {
        const num = parseFloat(amount) || 0;
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedProducts = [...this.topSellingProducts.map(p => p.productName)];
        } else {
            this.selectedProducts = [];
        }
    }

    toggleProductSelection(productName: string) {
        const index = this.selectedProducts.indexOf(productName);
        if (index > -1) {
            this.selectedProducts.splice(index, 1);
        } else {
            this.selectedProducts.push(productName);
        }
        this.selectAll = this.selectedProducts.length === this.topSellingProducts.length;
    }

    createBulkOrderVoucher() {
        if (this.selectedProducts.length === 0) {
            this.$toast.add({ 
                severity: 'warn', 
                summary: 'Warning', 
                detail: 'Please select at least one product', 
                life: 3000 
            });
            return;
        }
        
        this.reportService.getBulkOrderVoucherData({ productNames: this.selectedProducts })
            .then((res: any) => {
                this.generateBulkVoucherPDF(res.groupedBySupplier);
            });
    }

    generateBulkVoucherPDF(groupedBySupplier: any) {
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Please allow popups', life: 3000 });
            return;
        }
        
        const currentDate = new Date().toLocaleString('en-GB', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hour12: true
        });
        
        let vouchersHTML = '';
        
        Object.keys(groupedBySupplier).forEach((supplierName, index) => {
            const products = groupedBySupplier[supplierName];
            
            const productsRows = products.map((p: any, idx: number) => `
                <tr>
                    <td>${p.productName}</td>
                    <td>${p.brandName}</td>
                    <td>${p.currentStock}</td>
                    <td><input type="number" class="order-input" id="strip_${index}_${idx}" min="0" /></td>
                    <td><input type="number" class="order-input" id="pack_${index}_${idx}" min="0" /></td>
                    <td><input type="number" class="order-input" id="qty_${index}_${idx}" min="0" /></td>
                </tr>
            `).join('');
            
            const emptyRows = Math.max(0, 10 - products.length);
            const emptyRowsHTML = Array(emptyRows).fill(0).map((_, idx) => `
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="number" class="order-input" min="0" /></td>
                    <td><input type="number" class="order-input" min="0" /></td>
                    <td><input type="number" class="order-input" min="0" /></td>
                </tr>
            `).join('');
            
            vouchersHTML += `
                <div class="voucher-page" ${index > 0 ? 'style="page-break-before: always;"' : ''}>
                    <div class="header">
                        <h1>ORDER VOUCHER</h1>
                        <div class="header-info">
                            <div>Supplier: ${supplierName}</div>
                            <div>Date: ${currentDate}</div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Brand Name</th>
                                <th>Current Stock</th>
                                <th>Order Strip Size</th>
                                <th>Order Pack Size</th>
                                <th class="qty-col">Order Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${productsRows}
                            ${emptyRowsHTML}
                        </tbody>
                    </table>
                    <div class="footer">
                        <div class="signature">Authorized Signature</div>
                    </div>
                </div>
            `;
        });
        
        const html = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Bulk Order Vouchers</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .voucher-page { margin-bottom: 40px; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
                    .header h1 { font-size: 28px; color: #333; margin-bottom: 10px; }
                    .header-info { display: flex; justify-content: space-between; margin-top: 15px; font-size: 14px; font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #333; padding: 8px; text-align: left; }
                    th { background-color: #f0f0f0; font-weight: bold; }
                    .qty-col { width: 100px; }
                    .order-input { width: 100%; padding: 5px; border: 1px solid #ccc; text-align: center; font-size: 14px; }
                    .footer { margin-top: 50px; text-align: right; }
                    .signature { margin-top: 80px; border-top: 2px solid #333; width: 200px; float: right; padding-top: 10px; text-align: center; }
                    @media print {
                        body { padding: 20px; }
                        button { display: none; }
                        .voucher-page { page-break-after: always; }
                        .voucher-page:last-child { page-break-after: auto; }
                        .order-input { border: none; }
                    }
                    .print-btn { margin: 20px 0; padding: 10px 20px; background: #667eea; color: white; border: none; cursor: pointer; font-size: 16px; border-radius: 5px; }
                    .print-btn:hover { background: #5568d3; }
                </style>
            </head>
            <body>
                <button class="print-btn" onclick="window.print()">Print All Vouchers</button>
                ${vouchersHTML}
            </body>
            </html>
        `;
        
        printWindow.document.write(html);
        printWindow.document.close();
        
        this.$toast.add({ 
            severity: 'success', 
            summary: 'Success', 
            detail: `Generated ${Object.keys(groupedBySupplier).length} order voucher(s)`, 
            life: 3000 
        });
    }

    get historyList() {
        return this.lists.map((e: any) => ({
            ...e,
            receiptDate: this.formatDate(e.receiptDate),
        }));
    }
}
</script>

<style scoped>


.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.summary-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.card-blue .card-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.card-green .card-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.card-orange .card-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.card-purple .card-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.card-content h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
}

.card-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9rem;
}



.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
}

.receipt-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
}

.qty-badge {
    background: #f3e5f5;
    color: #7b1fa2;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

.price-text {
    color: #1565c0;
    font-weight: 600;
}

.amount-text {
    color: #2e7d32;
    font-weight: 700;
    font-size: 1.05rem;
}

/* Top Selling Products Section */
.top-selling-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.filter-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

.section-header > div {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-header i {
    font-size: 1.5rem;
    color: #667eea;
}

.section-header h4 {
    margin: 0;
    font-size: 1.3rem;
    color: #333;
}

.category-tag {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}

.status-hot {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    animation: pulse 2s infinite;
}

.status-high {
    background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
    color: white;
}

.status-good {
    background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
    color: white;
}

.status-moderate {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.status-normal {
    background: #e9ecef;
    color: #495057;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}



.p-datatable .p-datatable-tbody > tr {
    cursor: pointer;
}

/* Product Details Section */
.product-details-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.details-header h3 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.details-header h3 i {
    color: #667eea;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
}

.detail-item span {
    font-size: 1.1rem;
    color: #333;
    font-weight: 600;
}

.stock-value {
    color: #2e7d32;
    font-size: 1.3rem !important;
}

.recommend-value {
    color: #ff6b6b;
    font-size: 1.3rem !important;
}

</style>
