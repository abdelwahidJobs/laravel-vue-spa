<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SpaInstallCommand extends Command
{
    protected $signature = 'spa:install 
        {--with-product : Install the Product CRUD module}
        {--force : Overwrite existing files}';

    protected $description = 'Professional Scaffolder with API/Auth Service separation';

    public function handle()
    {
        $this->info('🚀 Starting SPA Scaffold Engine...');

        $this->updateNodePackages();
        $this->configureSanctum();
        $this->configureKernel();
        $this->configureCors();
        $this->configureEnv();
        $this->scaffoldVueBase();

        if ($this->option('with-product')) {
            $this->installProductModule();
        }

        $this->newLine();
        $this->info('✅ Setup Complete!');
        $this->warn('👉 Run: npm install && npm run dev');
    }

    protected function updateNodePackages()
    {
        $this->info('📦 Updating package.json...');
        $path = base_path('package.json');
        $packages = json_decode(File::get($path), true);

        $packages['dependencies'] = array_merge($packages['dependencies'] ?? [], [
            'vue' => '^2.6.14',
            'vue-router' => '^3.5.3',
            'axios' => '^1.3.0',
        ]);

        File::put($path, json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    protected function configureSanctum()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Laravel\Sanctum\SanctumServiceProvider',
            '--force' => true,
        ]);
    }

    protected function configureKernel()
    {
        $path = app_path('Http/Kernel.php');
        if (!File::exists($path)) return;

        $content = File::get($path);
        $middleware = '\\Laravel\\Sanctum\\Http\\Middleware\\EnsureFrontendRequestsAreStateful::class,';

        if (!str_contains($content, 'EnsureFrontendRequestsAreStateful')) {
            $content = str_replace("'api' => [", "'api' => [\n            $middleware", $content);
            File::put($path, $content);
        }
    }

    protected function configureCors()
    {
        $path = config_path('cors.php');
        if (File::exists($path)) {
            $content = File::get($path);
            $content = str_replace("'supports_credentials' => false", "'supports_credentials' => true", $content);
            File::put($path, $content);
        }
    }

    protected function configureEnv()
    {
        $path = base_path('.env');
        $content = File::get($path);

        if (!str_contains($content, 'SANCTUM_STATEFUL_DOMAINS')) {
            $envData = "\nSANCTUM_STATEFUL_DOMAINS=127.0.0.1,localhost\nSESSION_DOMAIN=127.0.0.1\n";
            File::append($path, $envData);
        }
    }

    protected function scaffoldVueBase()
    {
        $this->info('🖼 Creating Vue Structure...');
        $base = resource_path('js');

        foreach (['views', 'router', 'services'] as $dir) {
            File::ensureDirectoryExists("$base/$dir");
        }

        // app.js
        File::put("$base/app.js", "import './bootstrap';\nimport Vue from 'vue';\nimport router from './router';\nimport App from './views/App.vue';\n\nnew Vue({ el: '#app', router, render: h => h(App) });");

        // views/App.vue
        File::put("$base/views/App.vue", "<template>\n  <div id=\"app\">\n    <router-view></router-view>\n  </div>\n</template>");

        // router/index.js
        File::put("$base/router/index.js", $this->getRouterStub());

        // services/api.js (The Axios Instance)
        File::put("$base/services/api.js", $this->getApiBaseStub());

        // services/auth.js (The Auth Logic)
        File::put("$base/services/auth.js", $this->getAuthServiceStub());

        // views/Login.vue (Your Custom Styled Component)
        File::put("$base/views/Login.vue", $this->getLoginVueStub());
    }

    protected function installProductModule()
    {
        $this->info('✨ Injecting Product Module...');
        $this->call('make:model', ['name' => 'Product', '-m' => true]);

        $routerPath = resource_path('js/router/index.js');
        $routerContent = File::get($routerPath);
        $productRoute = "{ path: '/products', name: 'products.index', component: () => import('../views/Products.vue') },";

        if (!str_contains($routerContent, 'products.index')) {
            $routerContent = str_replace('const routes = [', "const routes = [\n  $productRoute", $routerContent);
            File::put($routerPath, $routerContent);
        }

        $apiPath = base_path('routes/api.php');
        $apiContent = File::get($apiPath);
        if (!str_contains($apiContent, 'products')) {
            File::append($apiPath, "\nRoute::middleware('auth:sanctum')->apiResource('products', App\Http\Controllers\Api\ProductController::class);");
        }
    }

    // --- STUBS ---

    protected function getApiBaseStub() {
        return "import axios from 'axios';\n\nconst api = axios.create({\n  withCredentials: true,\n  headers: {\n    'X-Requested-With': 'XMLHttpRequest',\n    'Accept': 'application/json'\n  }\n});\n\nexport default api;";
    }

    protected function getAuthServiceStub() {
        return <<<JS
import api from './api';

export default {
    async login(credentials) {
        // Sanctum CSRF cookie
        await api.get('/sanctum/csrf-cookie');
        return api.post('/api/login', credentials);
    },

    async user() {
        return api.get('/api/user');
    },

    logout() {
        return api.post('/api/logout');
    }
};
JS;
    }

    protected function getRouterStub() {
        return "import Vue from 'vue';\nimport VueRouter from 'vue-router';\nVue.use(VueRouter);\n\nconst routes = [\n  { path: '/', redirect: '/login' },\n  { path: '/login', name: 'login', component: () => import('../views/Login.vue') }\n];\n\nexport default new VueRouter({ mode: 'history', routes });";
    }

    protected function getLoginVueStub() {
        return <<<'VUE'
<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="h-12 w-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Welcome back
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Or
        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
          create a new account
        </a>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-xl border border-gray-100 sm:rounded-2xl sm:px-10">
        <form @submit.prevent="handleLogin" class="space-y-6">

          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700">Email address</label>
            <div class="mt-1">
              <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="you@example.com"
                  class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150"
              >
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
            <div class="mt-1">
              <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  placeholder="••••••••"
                  class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150"
              >
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input id="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
              <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
            </div>
            <div class="text-sm">
              <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>
          </div>

          <div>
            <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-4 mt-4 border-l-4 border-red-400">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import Auth from "../services/auth";
export default {
  data() {
    return {
      form: { email: 'test@example.com', password: 'password' },
      loading: false,
      error: null
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.error = null;
      try {
        await Auth.login(this.form);
        this.$router.push('/products');
      } catch (err) {
        this.error = "Invalid email or password.";
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
VUE;
    }
}