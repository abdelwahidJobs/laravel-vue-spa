<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SpaInstallCommand extends Command
{
    protected $signature = 'spa:install 
        {--with-product : Install the Product CRUD module}
        {--force : Overwrite existing files}';

    protected $description = 'One-command setup for Laravel 10/11 + Vue 2 SPA';

    public function handle()
    {
        $this->info('🚀 Initializing Professional SPA Scaffold (PHP 8.2+)...');

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
        $this->info('✅ SUCCESS: SPA Scaffolded!');
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
        $this->info('🖼 Creating Vue Directory Structure...');
        $base = resource_path('js');

        foreach (['views', 'router', 'services'] as $dir) {
            File::ensureDirectoryExists("$base/$dir");
        }

        // app.js
        File::put("$base/app.js", "import './bootstrap';\nimport Vue from 'vue';\nimport router from './router';\nimport App from './views/App.vue';\n\nnew Vue({ el: '#app', router, render: h => h(App) });");

        // App.vue
        File::put("$base/views/App.vue", "<template>\n  <div id=\"app\">\n    <router-view></router-view>\n  </div>\n</template>");

        // router/index.js
        File::put("$base/router/index.js", "import Vue from 'vue';\nimport VueRouter from 'vue-router';\nVue.use(VueRouter);\n\nconst routes = [\n  { path: '/login', name: 'login', component: () => import('../views/Login.vue') }\n];\n\nexport default new VueRouter({ mode: 'history', routes });");

        // Login.vue (Minimalist)
        File::put("$base/views/Login.vue", "<template>\n  <div class=\"flex items-center justify-center min-h-screen bg-gray-100\">\n    <form @submit.prevent=\"handleLogin\" class=\"p-8 bg-white shadow-lg rounded-lg\">\n      <h2 class=\"text-2xl font-bold mb-4\">Login</h2>\n      <input v-model=\"email\" type=\"email\" placeholder=\"Email\" class=\"block w-full border p-2 mb-2\" />\n      <input v-model=\"password\" type=\"password\" placeholder=\"Password\" class=\"block w-full border p-2 mb-4\" />\n      <button class=\"w-full bg-indigo-600 text-white py-2\">Sign In</button>\n    </form>\n  </div>\n</template>\n\n<script>\nimport axios from 'axios';\nexport default {\n  data() { return { email: '', password: '' } },\n  methods: {\n    async handleLogin() {\n      await axios.get('/sanctum/csrf-cookie');\n      await axios.post('/login', { email: this.email, password: this.password });\n      this.\$router.push('/products');\n    }\n  }\n}\n</script>");
    }

    protected function installProductModule()
    {
        $this->info('✨ Installing Product CRUD module...');

        // 1. Create Model & Migration
        $this->call('make:model', ['name' => 'Product', '-m' => true]);

        // 2. Inject Route into router/index.js (Before login to avoid conflict)
        $routerPath = resource_path('js/router/index.js');
        $routerContent = File::get($routerPath);
        $productRoute = "{ path: '/products', name: 'products.index', component: () => import('../views/Products.vue') },";

        if (!str_contains($routerContent, 'products.index')) {
            $routerContent = str_replace('const routes = [', "const routes = [\n  $productRoute", $routerContent);
            File::put($routerPath, $routerContent);
        }

        // 3. Inject API Route
        $apiPath = base_path('routes/api.php');
        $apiContent = File::get($apiPath);
        if (!str_contains($apiContent, 'products')) {
            File::append($apiPath, "\nRoute::middleware('auth:sanctum')->apiResource('products', App\Http\Controllers\Api\ProductController::class);");
        }
    }
}