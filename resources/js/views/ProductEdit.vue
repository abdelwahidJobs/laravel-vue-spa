<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        <p class="mt-4 text-gray-500">Loading product details...</p>
      </div>

      <div v-else>
        <div class="mb-8 flex items-center justify-between">
          <div>
            <button @click="$router.push('/products')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 flex items-center gap-1">
              ← Back to Products
            </button>
            <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Edit Product</h1>
          </div>
        </div>

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
          <form @submit.prevent="updateProduct" class="p-8 space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700">Product Name</label>
              <input v-model="form.name" type="text" required
                     class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700">Price ($)</label>
              <input v-model="form.price" type="number" step="0.01" required
                     class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700">URL Slug</label>
              <input :value="form.slug" type="text" disabled
                     class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg text-gray-500 sm:text-sm p-3 border cursor-not-allowed"
              />
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
              <button type="button" @click="$router.push('/products')"
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="saving"
                      class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Update Product' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProductService from "../services/product";

export default {
  props: ['slug'],
  data() {
    return {
      // Initialize with structure to avoid deep undefined errors
      form: {
        name: '',
        price: '',
        slug: ''
      },
      saving: false,
      loading: true
    }
  },
  async created() {
    try {
      const response = await ProductService.findBySlug(this.slug);

      this.form = response.data || response;

    } catch (error) {
      console.error("Fetch error:", error);
      alert("Product not found");
      this.$router.push('/products');
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async updateProduct() {
      this.saving = true;
      try {
        // Use your service here too for consistency!
        await ProductService.update(this.slug, this.form);
        this.$router.push('/products');
      } catch (error) {
        alert("Failed to update product.");
      } finally {
        this.saving = false;
      }
    }
  }
}
</script>