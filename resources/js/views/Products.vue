<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <span class="text-xl font-bold text-indigo-600 tracking-tight">Product Manager</span>
          <button @click="handleLogout" class="text-sm font-medium text-gray-500 hover:text-red-600 transition">
            Logout
          </button>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">All Products</h1>
          <p class="mt-2 text-sm text-gray-700">A list of all products currently in your database.</p>
        </div>
        <div class="mt-4 sm:mt-0">
          <router-link
              :to="{ name: 'product.create' }"
              class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add product
          </router-link>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-200 border-t-indigo-600"></div>
      </div>

      <div v-else class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg bg-white">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added On</th>
                  <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">#{{ product.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-900">{{ product.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                        ${{ product.price }}
                      </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(product.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                    <router-link :to="{ name: 'product.edit', params: { slug: product.slug } }" class="text-indigo-600 hover:text-indigo-900">
                      Edit
                    </router-link>
                    <button @click="deleteProduct(product.slug)" class="text-red-600 hover:text-red-900">
                      Delete
                    </button>
                  </td>
                </tr>
                </tbody>
              </table>

              <div v-if="products.length === 0" class="text-center py-12">
                <p class="text-gray-500">No products found.</p>
              </div>

              <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                  <button @click="getProducts(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn-pagination">Previous</button>
                  <button @click="getProducts(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn-pagination ml-3">Next</button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Showing page <span class="font-medium">{{ pagination.current_page }}</span> of <span class="font-medium">{{ pagination.last_page }}</span>
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                      <button
                          @click="getProducts(pagination.current_page - 1)"
                          :disabled="pagination.current_page === 1"
                          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                      >
                        Previous
                      </button>
                      <button
                          @click="getProducts(pagination.current_page + 1)"
                          :disabled="pagination.current_page === pagination.last_page"
                          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                      >
                        Next
                      </button>
                    </nav>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import ProductService from "../services/product";

export default {
  data() {
    return {
      products: [],
      loading: true,
      pagination: {
        current_page: 1,
        last_page: 1
      }
    };
  },
  async created() {
    await this.getProducts(1);
  },
  methods: {
    async getProducts(page) {
      this.loading = true;
      try {
        const response = await ProductService.getAll(page);
        // Assuming your service returns the Laravel pagination object
        this.products = response.data; // This is the array of 6 items
        this.pagination.current_page = response.current_page;
        this.pagination.last_page = response.last_page;
      } catch (error) {
        console.error("Error fetching products:", error);
      } finally {
        this.loading = false;
      }
    },

    async deleteProduct(slug) {
      if (!confirm("Are you sure you want to delete this product?")) return;

      try {
        await ProductService.delete(slug);
        // Automatically refill to 6 items by fetching the current page again
        await this.getProducts(this.pagination.current_page);
      } catch (error) {
        alert("Failed to delete product.");
      }
    },

    async handleLogout() {
      // Your logout logic (e.g., Auth.logout())
      this.$router.push("/login");
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }
  }
};
</script>

<style scoped>
.btn-pagination {
  @apply relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50;
}
</style>

<script>

import axios from "axios";
import Auth from "../services/auth"
import ProductService from "../services/product";
export default {
  data() {
    return {
      products: [],
      loading: true,
      pagination: {
        current_page: 1,
        last_page: 1
      },
    }
  },
  created() {
    this.getProducts(1);
  },
  methods: {
    async deleteProduct(slug)
    {
      this.loading = true;
      if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        return;
      }
      try {
        await ProductService.delete(slug);
        this.products = this.products.filter(product => product.slug !== slug);
        console.log('Product deleted successfully');
        this.loading = false;

        // 1. Calculate if we just emptied the current page
        // If we are on a page higher than 1 AND we only have 1 item left in the list...
        let targetPage = this.pagination.current_page;

        if (this.pagination.current_page > 1 && this.products.length == 0) {
          targetPage = this.pagination.current_page - 1;
        }
        // RE-FETCH: This pulls the fresh list (with 6 items) from the server
        await this.getProducts(targetPage);
      }catch (error) {
        console.error("Delete failed:", error);
        alert("Could not delete the product. It might be linked to other data.");
      }
    },
    async getProducts(page) {
      this.loading = true;
      try {
        const response = await ProductService.getProducts(page);
        console.log("Full Response Object:", response);
        this.products = response.data;
        this.pagination.current_page = response.current_page;
        this.pagination.last_page = response.last_page;
      } catch (error) {
        console.error("Error fetching products:", error);
      } finally {
        this.loading = false;
      }
    },
    async handleLogout() {
      try {
        await Auth.logout();
        this.$router.push('/login');
      } catch (error) {
        console.error("Logout failed:", error);
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    }
  }
}
</script>