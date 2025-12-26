<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
      <div class="mb-8">
        <button @click="$router.push('/products')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 flex items-center gap-1">
          ← Back to List
        </button>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Add New Product</h1>
      </div>

      <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <form @submit.prevent="storeProduct" class="p-8 space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700">Product Name</label>
            <input v-model="form.name" type="text" placeholder="e.g. Wireless Headphones" required
                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700">Price ($)</label>
            <input v-model="form.price" type="number" step="0.01" placeholder="0.00" required
                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 border" />
          </div>

          <div class="pt-4 flex items-center justify-end gap-3 border-t">
            <button type="button" @click="$router.push('/products')" class="px-4 py-2 text-sm text-gray-700">Cancel</button>
            <button type="submit" :disabled="saving" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
              {{ saving ? 'Creating...' : 'Create Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import ProductService from "../services/product";

export default {
  data() {
    return {
      form: { name: '', price: '' },
      saving: false
    };
  },
  methods: {
    async storeProduct() {
      this.saving = true;
      try {
        await ProductService.create(this.form);
        this.$router.push('/products');
      } catch (error) {
        alert("Error creating product. Make sure the name is unique.");
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>