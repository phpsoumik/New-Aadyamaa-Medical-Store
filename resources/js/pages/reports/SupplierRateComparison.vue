<!-- THIS IS SOUMIK CODE - Supplier Rate Comparison Tool -->
<template>
  <div class="app-container">
    <div class="jumbotron p-1 text-center mb-0">
      <h5><i class="pi pi-chart-line"></i> Supplier Rate Comparison Tool</h5>
      <h6 class="font-weight-bold">Flag items with identical MRP but different purchase rates</h6>
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
          <p>Loading comparison data...</p>
        </div>

        <!-- No Data State -->
        <div v-else-if="flaggedProducts.length === 0" class="text-center py-5">
          <i class="pi pi-check-circle" style="font-size: 3rem; color: green"></i>
          <h5 class="mt-3">No Rate Differences Found</h5>
          <p>All products have consistent purchase rates across suppliers.</p>
        </div>

        <!-- PrimeVue DataTable with built-in search and pagination -->
        <DataTable
          v-else
          :value="flaggedProducts"
          :paginator="true"
          :rows="50"
          :rowsPerPageOptions="[25, 50, 100]"
          :globalFilterFields="['product_name']"
          v-model:filters="filters"
          filterDisplay="row"
          class="p-datatable-sm"
        >
          <template #header>
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="pi pi-flag"></i> Flagged Products
                <span class="badge badge-warning ml-2">{{ flaggedProducts.length }} Products Found</span>
              </h6>
              <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters['global'].value" placeholder="Search products..." />
              </span>
            </div>
          </template>
          
          <Column field="product_name" header="Product Name" :sortable="true">
            <template #body="{data}">
              <strong>{{ data.product_name }}</strong>
            </template>
          </Column>
          
          <Column field="mrp" header="MRP" :sortable="true">
            <template #body="{data}">
              ₹{{ formatAmount(data.mrp) }}
            </template>
          </Column>
          
          <Column field="min_rate" header="Min Rate" :sortable="true">
            <template #body="{data}">
              <span class="text-success">₹{{ formatAmount(data.min_rate) }}</span>
            </template>
          </Column>
          
          <Column field="max_rate" header="Max Rate" :sortable="true">
            <template #body="{data}">
              <span class="text-danger">₹{{ formatAmount(data.max_rate) }}</span>
            </template>
          </Column>
          
          <Column field="rate_difference" header="Difference" :sortable="true">
            <template #body="{data}">
              <span class="text-warning">₹{{ formatAmount(data.rate_difference) }}</span>
            </template>
          </Column>
          
          <Column header="Suppliers">
            <template #body="{data}">
              {{ data.suppliers.length }} Suppliers
            </template>
          </Column>
          
          <Column header="Action">
            <template #body="{data}">
              <Button 
                icon="pi pi-eye" 
                class="p-button-sm p-button-info"
                @click="viewDetails(data)"
                label="View"
              />
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <i class="pi pi-info-circle"></i> Supplier Rate Details
            </h5>
            <button type="button" class="close text-white" @click="closeModal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body" v-if="selectedProduct">
            <h6><strong>Product:</strong> {{ selectedProduct.product_name }}</h6>
            <h6><strong>MRP:</strong> ₹{{ formatAmount(selectedProduct.mrp) }}</h6>
            <hr>
            <h6 class="mb-3">Supplier Rates:</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Supplier Name</th>
                    <th>Purchase Rate</th>
                    <th>Batch No</th>
                    <th>Expiry Date</th>
                    <th>Bill No</th>
                    <th>Bill Date</th>
                    <th>Current Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(supplier, idx) in selectedProduct.suppliers" :key="idx"
                      :class="{
                        'table-success': supplier.purchase_price == selectedProduct.min_rate,
                        'table-danger': supplier.purchase_price == selectedProduct.max_rate
                      }">
                    <td><strong>{{ supplier.supplier_name }}</strong></td>
                    <td>₹{{ formatAmount(supplier.purchase_price) }}</td>
                    <td>{{ supplier.batch_no }}</td>
                    <td>{{ supplier.expiry_date }}</td>
                    <td>{{ supplier.bill_no }}</td>
                    <td>{{ supplier.bill_date }}</td>
                    <td>{{ supplier.current_stock }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="alert alert-info mt-3">
              <strong>Note:</strong> 
              <span class="text-success">Green</span> = Lowest Rate | 
              <span class="text-danger">Red</span> = Highest Rate
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Options, mixins } from "vue-class-component";
import UtilityOptions from "../../mixins/UtilityOptions";
import axios from "axios";
import { FilterMatchMode } from 'primevue/api';

@Options({
  title: 'Supplier Rate Comparison Tool',
})

export default class SupplierRateComparison extends mixins(UtilityOptions) {
  private loading = false;
  private flaggedProducts: any[] = [];
  private selectedProduct: any = null;
  private filters = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
  };

  mounted() {
    this.loadComparisonData();
  }

  async loadComparisonData() {
    this.loading = true;
    try {
      const response = await axios.get('/api/supplier_rate_comparison');
      if (response.data.success) {
        this.flaggedProducts = response.data.data;
      }
    } catch (error) {
      console.error('Error loading comparison data:', error);
      alert('Error loading data');
    } finally {
      this.loading = false;
    }
  }

  viewDetails(product: any) {
    this.selectedProduct = product;
    const modal = document.getElementById('detailsModal');
    if (modal) {
      (modal as any).style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');
    }
  }

  closeModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) {
      (modal as any).style.display = 'none';
      modal.classList.remove('show');
      document.body.classList.remove('modal-open');
    }
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border: none;
}

.table-success {
  background-color: #d4edda !important;
}

.table-danger {
  background-color: #f8d7da !important;
}

.badge-warning {
  background-color: #ffc107;
  color: #000;
}
</style>
