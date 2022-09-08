## 75. API通信の設定確認(Sanctum, axios)

### APIの動作確認

+ `Inertia::render`で返却するとページ毎 再描画される<br>

+ 顧客の箇所だけ更新したいのでAPIで対応 Sanctumの機能を使う<br>

+ [Laravel Sanctum](https://readouble.com/laravel/9.x/ja/sanctum.html) <br>

+ [`.env`の関連記事](https://qiita.com/ksrnnb/items/d2b73a6bf7dccde90446) <br>

+ `app/Http/Kernel.php`を編集<br>

```php:Kernal.php
// 省略
 protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // コメントアウト解除
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
// 省略
```

+ `.env`を編集<br>

```:.env
APP_NAME=uCRM
APP_ENV=local
APP_KEY=base64:BQ8RM6HfFzPbQl3QFh5cE/UkKVLW0z54VywpnuhkV+Y=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_vue3_udemy
DB_USERNAME=sail
DB_PASSWORD=password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=memcached

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700

# 追加
SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DOMAIN=localhost
```

+ `resources/js/Components/MicroModal.vue`を編集<br>

```vue:MicroModal.vue
<script setup>
import axios from 'axios'; // 追加
import { onMounted, ref } from 'vue'; // 編集

// 追加
onMounted(() => {
  axios.get('/api/user')
    .then((res) => {
      console.log(res.data)
    })
})
// ここまで

const isShow = ref(false)
const toggleStatus = () => {
  isShow.value = !isShow.value
}
</script>

<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            Micromodal
          </h2>
          <button type="button" @click="toggleStatus" class="modal__close" aria-label="Close modal"
            data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <p>
            Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also,
            <code>esc</code> to close modal.
          </p>
        </main>
        <footer class="modal__footer">
          <button type="button" @click="toggleStatus" class="modal__btn modal__btn-primary">Continue</button>
          <button type="button" @click="toggleStatus" class="modal__btn" data-micromodal-close
            aria-label="Close this dialog window">Close</button>
        </footer>
      </div>
    </div>
  </div>
  <button type="button" @click="toggleStatus" data-micromodal-trigger="modal-1" href='javascript:;'>Open Modal
    Dialog</button>
</template>
```