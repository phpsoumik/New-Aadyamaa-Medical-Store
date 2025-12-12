<template>
  <div class="app-container">
    <div class="jumbotron p-1 text-center mb-0">
      <h5><i class="pi pi-exclamation-triangle"></i> Expiry Return Report</h5>
      <h6 class="font-weight-bold">View expired products by supplier for return processing</h6>
    </div>

    <div class="card">
      <div class="card-body">
        <!-- Supplier Selection -->
        <div class="row mb-4">
          <div class="col-md-6">
            <label class="form-label">Select Supplier:</label>
            <select 
              v-model="selectedSupplierId" 
              @change="loadExpiryReturnReport" 
              class="form-control"
            >
              <option value="">-- Select Supplier --</option>
              <option 
                v-for="supplier in suppliers" 
                :key="supplier.id" 
                :value="supplier.id"
              >
                {{ supplier.name }}
              </option>
            </select>
          </div>
          <div class="col-md-6" v-if="reportData.total_products">
            <div class="card bg-danger text-white">
              <div class="card-body">
                <h5>{{ reportData.total_products || 0 }}</h5>
                <p>Total Expired Products</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Products List -->
        <div v-if="loading" class="text-center">
          <i class="pi pi-spin pi-spinner" style="font-size: 2rem"></i>
          <p>Loading expired products...</p>
        </div>

        <div v-else-if="reportData.data && reportData.data.bills && reportData.data.bills.length > 0">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0">
                <i class="pi pi-building"></i> {{ reportData.data.supplier_name }}
                <span class="badge badge-light ml-2">{{ reportData.data.bills.length }} Bills</span>
              </h6>
            </div>
            <div class="card-body">
              <!-- Bills List -->
              <div v-for="bill in reportData.data.bills" :key="bill.bill_no" class="mb-3">
                <div class="card border">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                      <strong>Bill No: {{ bill.bill_no }}</strong>
                      <span class="ml-3 text-muted">{{ bill.total_products }} Products</span>
                      <span class="ml-3 text-success">Total: Rs.{{ formatAmount(bill.total_value) }}</span>
                    </div>
                    <button 
                      @click="toggleBillDetails(bill.bill_no)" 
                      class="btn btn-sm btn-outline-primary mr-2"
                    >
                      {{ expandedBills.includes(bill.bill_no) ? 'Hide Details' : 'View All' }}
                    </button>
                    <!-- this is soumik code - Print button for each bill -->
                    <button 
                      @click="printBill(bill)" 
                      class="btn btn-sm btn-success"
                      title="Print Bill"
                    >
                      Print
                    </button>
                    <!-- this is soumik code - bulk delete button -->
                    <button 
                      v-if="selectedProducts.length > 0 && expandedBills.includes(bill.bill_no)"
                      @click="bulkDeleteProducts()" 
                      class="btn btn-sm btn-danger ml-2"
                    >
                      Delete Selected ({{ selectedProducts.length }})
                    </button>
                  </div>
                  
                  <!-- Products Details (Collapsible) -->
                  <div v-if="expandedBills.includes(bill.bill_no)" class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped mb-0">
                        <thead class="thead-light">
                          <tr>
                            <!-- this is soumik code - select all checkbox -->
                            <th>
                              <input 
                                type="checkbox" 
                                @change="selectAll($event, bill.products)"
                                :checked="isAllSelected(bill.products)"
                              /> Select All
                            </th>
                            <th>Product Name</th>
                            <th>Batch No</th>
                            <th>Expiry Date</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <!-- THIS IS SOUMIK CODE - Adding MRP column -->
                            <th>MRP</th>
                            <th>Total Value</th>
                            <!-- this is soumik code - adding action column for edit/delete -->
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="product in bill.products" :key="product.id">
                            <!-- this is soumik code - individual product checkbox -->
                            <td>
                              <input 
                                type="checkbox" 
                                v-model="selectedProducts"
                                :value="product.id"
                              />
                            </td>
                            <td>{{ product.product_name }}</td>
                            <td>{{ product.batch_no }}</td>
                            <td>
                              <span class="badge badge-danger">{{ product.expiry_date }}</span>
                            </td>
                            <td>{{ product.qty }}</td>
                            <td>Rs.{{ formatAmount(product.purchase_price) }}</td>
                            <!-- THIS IS SOUMIK CODE - Displaying MRP -->
                            <td><strong>Rs.{{ formatAmount(product.mrp || 0) }}</strong></td>
                            <td>Rs.{{ formatAmount(product.total_value) }}</td>
                            <!-- this is soumik code - simple edit and delete buttons -->
                            <td>
                              <button 
                                @click="editProduct(product)" 
                                class="btn btn-sm btn-warning mr-1"
                                title="Edit Product"
                              >
                                Edit
                              </button>
                              <button 
                                @click="deleteProduct(product)" 
                                class="btn btn-sm btn-danger"
                                title="Delete Product"
                              >
                                Delete
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="selectedSupplierId && !loading" class="text-center py-5">
          <i class="pi pi-check-circle" style="font-size: 3rem; color: green"></i>
          <h5 class="mt-3">No Expired Products Found</h5>
          <p>This supplier has no expired products.</p>
        </div>

        <div v-else-if="!selectedSupplierId" class="text-center py-5">
          <i class="pi pi-info-circle" style="font-size: 3rem; color: #007bff"></i>
          <h5 class="mt-3">Select a Supplier</h5>
          <p>Please select a supplier to view their expired products.</p>
        </div>
      </div>
    </div>

    <!-- this is soumik code - Bootstrap Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Expiry Return Product</h5>
            <button type="button" class="close" @click="closeEditModal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Product Name:</label>
              <input type="text" v-model="editForm.product_name" class="form-control" />
            </div>
            <div class="form-group">
              <label>Batch No:</label>
              <input type="text" v-model="editForm.batch_no" class="form-control" />
            </div>
            <div class="form-group">
              <label>Quantity:</label>
              <input type="number" v-model="editForm.ret_quantity" class="form-control" min="0" step="0.01" />
            </div>
            <div class="form-group">
              <label>Purchase Price:</label>
              <input type="number" v-model="editForm.purchase_price" class="form-control" min="0" step="0.01" />
            </div>
            <div class="form-group">
              <label>Expiry Date:</label>
              <input type="date" v-model="editForm.exp_date" class="form-control" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeEditModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="updateProduct">Update Product</button>
          </div>
        </div>
      </div>
    </div>

    <!-- this is soumik code - Bootstrap Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="close" @click="closeDeleteModal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this expiry return record?</p>
            <p><strong>Product:</strong> {{ deleteForm.product_name }}</p>
            <p><strong>Batch:</strong> {{ deleteForm.batch_no }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
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
//this is soumik code - only Dialog component import
import Dialog from 'primevue/dialog';

@Options({
  title: 'Expiry Return Report',
  //this is soumik code - only Dialog component
  components: {
    Dialog
  },
})

export default class ExpiryReturnReport extends mixins(UtilityOptions) {
  private loading = false;
  private suppliers: any[] = [];
  private selectedSupplierId = '';
  private expandedBills: string[] = [];
  private reportData: any = {
    data: null,
    total_products: 0
  };
  //this is soumik code - edit and delete form properties with modal visibility
  private editModalVisible = false;
  private deleteModalVisible = false;
  private editForm: any = {
    id: null,
    product_name: '',
    batch_no: '',
    ret_quantity: 0,
    purchase_price: 0,
    exp_date: null
  };
  private deleteForm: any = {
    id: null,
    product_name: '',
    batch_no: ''
  };
  //this is soumik code - bulk delete properties
  private selectedProducts: number[] = [];
  private bulkDeleteModalVisible = false;

  mounted() {
    this.loadSuppliers();
  }

  async loadSuppliers() {
    try {
      const response = await axios.get('/api/suppliers');
      
      if (response.data.success) {
        this.suppliers = response.data.data;
      } else {
        alert('Failed to load suppliers');
      }
    } catch (error) {
      console.error('Error loading suppliers:', error);
      alert('Error loading suppliers');
    }
  }

  async loadExpiryReturnReport() {
    if (!this.selectedSupplierId) {
      this.reportData = { data: null, total_products: 0 };
      this.expandedBills = [];
      return;
    }

    this.loading = true;
    this.expandedBills = [];
    try {
      const response = await axios.post('/api/expiry_return_report', {
        supplier_id: this.selectedSupplierId
      });
      
      if (response.data.success) {
        this.reportData = response.data;
      } else {
        alert('Failed to load expiry return report');
      }
    } catch (error) {
      console.error('Error loading expiry return report:', error);
      alert('Error loading expiry return report');
    } finally {
      this.loading = false;
    }
  }

  toggleBillDetails(billNo: string) {
    const index = this.expandedBills.indexOf(billNo);
    if (index > -1) {
      this.expandedBills.splice(index, 1);
    } else {
      this.expandedBills.push(billNo);
    }
  }

  //this is soumik code - edit product method with simple dialog
  editProduct(product: any) {
    this.editForm = {
      id: product.id,
      product_name: product.product_name,
      batch_no: product.batch_no,
      ret_quantity: product.qty,
      purchase_price: product.purchase_price,
      exp_date: this.formatDateForInput(product.expiry_date)
    };
    
    console.log('Edit button clicked for product:', product);
    console.log('Edit form data:', this.editForm);
    // Open Bootstrap modal with native JS
    const modal = document.getElementById('editModal');
    if (modal) {
      (modal as any).style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');
    }
  }

  //this is soumik code - update product method with debugging
  async updateProduct() {
    try {
      console.log('Sending update data:', this.editForm);
      const response = await axios.post('/api/update_expiry_return', this.editForm);
      console.log('Update response:', response.data);
      
      if (response.data.success) {
        alert('Product updated successfully');
        // Close modal with native JS
        const modal = document.getElementById('editModal');
        if (modal) {
          (modal as any).style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
        }
        // Reload the report to show updated data
        await this.loadExpiryReturnReport();
      } else {
        console.error('Update failed:', response.data.message);
        alert('Failed to update product: ' + (response.data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error updating product:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
        alert('Error: ' + (error.response.data.message || error.message));
      } else {
        alert('Network error: ' + error.message);
      }
    }
  }

  //this is soumik code - delete product method with PrimeVue dialog
  deleteProduct(product: any) {
    this.deleteForm = {
      id: product.id,
      product_name: product.product_name,
      batch_no: product.batch_no
    };
    
    console.log('Delete button clicked for product:', product);
    console.log('Delete form data:', this.deleteForm);
    // Open Bootstrap delete confirmation modal with native JS
    const modal = document.getElementById('deleteModal');
    if (modal) {
      (modal as any).style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');
    }
  }

  //this is soumik code - confirm delete method with debugging
  async confirmDelete() {
    try {
      console.log('Deleting product ID:', this.deleteForm.id);
      const response = await axios.post('/api/delete_expiry_return', {
        id: this.deleteForm.id
      });
      console.log('Delete response:', response.data);
      
      if (response.data.success) {
        alert('Product deleted successfully');
        // Close modal with native JS
        const modal = document.getElementById('deleteModal');
        if (modal) {
          (modal as any).style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
        }
        // Reload the report to show updated data
        await this.loadExpiryReturnReport();
      } else {
        console.error('Delete failed:', response.data.message);
        alert('Failed to delete product: ' + (response.data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error deleting product:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
        alert('Error: ' + (error.response.data.message || error.message));
      } else {
        alert('Network error: ' + error.message);
      }
    }
  }

  //this is soumik code - helper method for date formatting
  formatDateForInput(dateStr: string): string {
    // Convert DD-MM-YYYY to YYYY-MM-DD for HTML date input
    const parts = dateStr.split('-');
    if (parts.length === 3) {
      return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
    }
    return dateStr;
  }

  //this is soumik code - close modal methods
  closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
      (modal as any).style.display = 'none';
      modal.classList.remove('show');
      document.body.classList.remove('modal-open');
    }
  }

  closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
      (modal as any).style.display = 'none';
      modal.classList.remove('show');
      document.body.classList.remove('modal-open');
    }
  }

  //this is soumik code - print individual bill method
  printBill(bill: any) {
    const printContent = this.generateBillPrintContent(bill);
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    if (printWindow) {
      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }
  }

  //this is soumik code - generate print content for individual bill
  generateBillPrintContent(bill: any): string {
    const supplierName = this.reportData.data?.supplier_name || 'Unknown Supplier';
    const currentDate = new Date().toLocaleDateString();
    
    let productsHtml = '';
    let totalValue = 0;
    
    bill.products.forEach((product: any) => {
      totalValue += product.total_value;
      productsHtml += `
        <tr>
          <td>${product.product_name}</td>
          <td>${product.batch_no}</td>
          <td>${product.expiry_date}</td>
          <td>${product.qty}</td>
          <td>Rs.${this.formatAmount(product.purchase_price)}</td>
          <td>Rs.${this.formatAmount(product.total_value)}</td>
        </tr>
      `;
    });
    
    return `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Expiry Return Report - Bill ${bill.bill_no}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
          .company-name { font-size: 24px; font-weight: bold; margin: 0; }
          .report-title { font-size: 18px; margin: 10px 0; }
          .bill-info { margin: 20px 0; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          th { background-color: #f2f2f2; font-weight: bold; }
          .total-row { font-weight: bold; background-color: #f9f9f9; }
          .print-date { text-align: right; margin-top: 20px; font-size: 12px; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1 class="company-name">Aadyamaa Medical Shop</h1>
          <h2 class="report-title">Expiry Return Report</h2>
        </div>
        
        <div class="bill-info">
          <p><strong>Supplier:</strong> ${supplierName}</p>
          <p><strong>Bill No:</strong> ${bill.bill_no}</p>
          <p><strong>Bill Date:</strong> ${bill.bill_date || 'N/A'}</p>
          <p><strong>Total Products:</strong> ${bill.total_products}</p>
        </div>
        
        <table>
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Batch No</th>
              <th>Expiry Date</th>
              <th>Quantity</th>
              <th>Purchase Price</th>
              <th>Total Value</th>
            </tr>
          </thead>
          <tbody>
            ${productsHtml}
            <tr class="total-row">
              <td colspan="5"><strong>Grand Total</strong></td>
              <td><strong>Rs.${this.formatAmount(totalValue)}</strong></td>
            </tr>
          </tbody>
        </table>
        
        <div class="print-date">
          <p>Printed on: ${currentDate}</p>
        </div>
      </body>
      </html>
    `;
  }

  //this is soumik code - select all functionality
  selectAll(event: any, products: any[]) {
    if (event.target.checked) {
      // Add all product IDs to selected array
      products.forEach(product => {
        if (!this.selectedProducts.includes(product.id)) {
          this.selectedProducts.push(product.id);
        }
      });
    } else {
      // Remove all product IDs from selected array
      products.forEach(product => {
        const index = this.selectedProducts.indexOf(product.id);
        if (index > -1) {
          this.selectedProducts.splice(index, 1);
        }
      });
    }
  }

  //this is soumik code - check if all products are selected
  isAllSelected(products: any[]): boolean {
    return products.every(product => this.selectedProducts.includes(product.id));
  }

  //this is soumik code - bulk delete products
  bulkDeleteProducts() {
    if (this.selectedProducts.length === 0) {
      alert('Please select products to delete');
      return;
    }

    const confirmMessage = `Are you sure you want to delete ${this.selectedProducts.length} selected products?`;
    if (confirm(confirmMessage)) {
      this.performBulkDelete();
    }
  }

  //this is soumik code - perform bulk delete operation using new API
  async performBulkDelete() {
    try {
      console.log('Bulk deleting products:', this.selectedProducts);
      
      // Use bulk delete API endpoint
      const response = await axios.post('/api/bulk_delete_expiry_return', {
        ids: this.selectedProducts
      });
      
      if (response.data.success) {
        alert(response.data.message);
        this.selectedProducts = []; // Clear selection
        await this.loadExpiryReturnReport(); // Reload data
      } else {
        alert('Bulk delete failed: ' + response.data.message);
      }
    } catch (error) {
      console.error('Error in bulk delete:', error);
      if (error.response) {
        alert('Error: ' + (error.response.data.message || error.message));
      } else {
        alert('Network error: ' + error.message);
      }
    }
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border: none;
}

.badge-danger {
  background-color: #dc3545;
}

.badge-light {
  background-color: #f8f9fa;
  color: #495057;
}

.table th {
  border-top: none;
  font-weight: 600;
}

.bg-primary {
  background-color: #007bff !important;
}

.bg-warning {
  background-color: #ffc107 !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}
</style>