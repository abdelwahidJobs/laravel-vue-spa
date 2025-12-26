<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SpaInstallCommand extends Command
{
    protected $signature = 'spa:install';
    protected $description = 'Install SPA scaffolding with Laravel Mix + Vue 2 + Tailwind';

    public function handle()
    {

        $this->configureEnv();
        $this->ensureNodeVersion();
        $this->createWebpackMix();
        $this->ensureMixScripts();
        $this->installNpmPackages();
        $this->createVueAssets();
        $this->createTailwindConfig();

        $this->configureSanctum();
        $this->configureKernel();
        $this->configureCors();
        $this->scaffoldVueBase();

        $this->info('SPA installed successfully with Laravel Mix + Vue 2!');
        $this->info('Run: npm run watch');
    }

    protected function configureEnv()
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            copy(base_path('.env.example'), $path);
            $this->info('.env file created from .env.example');
        }

        $content = File::get($path);
        if (!str_contains($content, 'SANCTUM_STATEFUL_DOMAINS')) {
            $envData = "\nSANCTUM_STATEFUL_DOMAINS=127.0.0.1,localhost\nSESSION_DOMAIN=127.0.0.1\n";
            File::append($path, $envData);
        }
    }

    protected function ensureNodeVersion()
    {
        exec('node -v', $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('Node.js is not installed.');
        }

        $version = ltrim($output[0], 'v');
        if (version_compare($version, '12.0.0', '<')) {
            $this->error('Node.js 12+ is required. Current: ' . $version);
            $this->line('Please install a compatible Node version using nvm.');
            exit(1);
        }
    }

    protected function createWebpackMix()
    {
        $mixPath = base_path('webpack.mix.js');

        if (!file_exists($mixPath)) {
            $content = <<<EOT
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 2 })
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
        require('autoprefixer'),
    ]);

mix.browserSync('127.0.0.1:8000');
EOT;

            file_put_contents($mixPath, $content);
            $this->info('webpack.mix.js created with Vue 2 + Tailwind CSS.');
        } else {
            $this->info('webpack.mix.js already exists.');
        }
    }

    protected function ensureMixScripts()
    {
        $packageJsonPath = base_path('package.json');
        $package = [];

        if (file_exists($packageJsonPath)) {
            $package = json_decode(file_get_contents($packageJsonPath), true);
        }

        $package['scripts'] ??= [];

        $package['scripts']['dev']         = "npm run development";
        $package['scripts']['development'] = "mix";
        $package['scripts']['watch']       = "mix watch";
        $package['scripts']['watch-poll']  = "mix watch -- --watch-options-poll=1000";
        $package['scripts']['hot']         = "mix watch --hot";
        $package['scripts']['prod']        = "npm run production";
        $package['scripts']['production']  = "mix --production";

        file_put_contents(
            $packageJsonPath,
            json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->info('Laravel Mix NPM scripts set in package.json.');
    }

    protected function installNpmPackages()
    {
        $this->info('Installing NPM dependencies for Laravel Mix + Vue 2...');
        exec('npm install vue@2 vue-router@3 laravel-mix cross-env axios tailwindcss autoprefixer --save-dev', $output, $code);

        if ($code !== 0) {
            throw new \RuntimeException('NPM install failed.');
        }

        $this->info('NPM dependencies installed successfully.');
    }

    protected function createVueAssets()
    {
        $jsPath = resource_path('js');
        $cssPath = resource_path('css');

        File::ensureDirectoryExists($jsPath);
        File::ensureDirectoryExists($cssPath);

        // Create app.css with Tailwind content if it doesn't exist
        $appCssPath = $cssPath . '/app.css';
        if (!file_exists($appCssPath)) {
            file_put_contents($appCssPath, "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n");
            $this->info('resources/css/app.css created with Tailwind directives.');
        }

        // JS setup
        if (!file_exists($jsPath . '/app.js')) {
            file_put_contents($jsPath . '/app.js', <<<EOT
import Vue from 'vue';
import App from './views/App.vue';
import router from './router';

new Vue({
    el: '#app',
    router,
    render: h => h(App)
});
EOT
            );
            $this->info('resources/js/app.js created.');
        }

        // Create Vue views
        $viewsPath = $jsPath . '/views';
        File::ensureDirectoryExists($viewsPath);

        if (!file_exists($viewsPath . '/App.vue')) {
            file_put_contents($viewsPath . '/App.vue', <<<EOT
<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: "App"
};
</script>

<style>
/* Global styles */
</style>
EOT
            );
            $this->info('resources/js/views/App.vue created.');
        }

        // Router setup
        $routerPath = $jsPath . '/router';
        File::ensureDirectoryExists($routerPath);

        if (!file_exists($routerPath . '/index.js')) {
            file_put_contents($routerPath . '/index.js', <<<EOT
import Vue from 'vue';
import Router from 'vue-router';
import Home from '../views/Home.vue';

Vue.use(Router);

export default new Router({
    mode: 'history',
    routes: [
        { path: '/', name: 'home', component: Home }
    ]
});
EOT
            );
            $this->info('resources/js/router/index.js created.');
        }

        if (!file_exists($viewsPath . '/Home.vue')) {
            file_put_contents($viewsPath . '/Home.vue', <<<EOT
<template>
  <div>
    <h1>Welcome to Vue SPA!</h1>
  </div>
</template>

<script>
export default {
  name: "Home"
};
</script>
EOT
            );
            $this->info('resources/js/views/Home.vue created.');
        }
    }

    protected function createTailwindConfig()
    {
        $path = base_path('tailwind.config.js');

        if (!file_exists($path)) {
            $content = <<<EOT
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
EOT;
            file_put_contents($path, $content);
            $this->info('tailwind.config.js created.');
        } else {
            $this->info('tailwind.config.js already exists.');
        }
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


        $base = resource_path();
        File::ensureDirectoryExists("$base/views");

        // views/Login.vue (Your Custom Styled Component)
        File::put("$base/views/app.blade.php", $this->getAppBladeStub());


        $base = resource_path('js');
        File::put("$base/bootstrap.js", $this->bootstrapSub());
    }

    public function bootstrapSub()
    {
        return <<<EOT
import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
EOT;
    }
    public function getAppBladeStub()
    {
        return <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vue SPA Sanctum</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app"></div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
BLADE;
    }

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
