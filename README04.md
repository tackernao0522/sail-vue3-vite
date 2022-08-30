## 19. Vue.js3の概要

### Vue.js SingleFileComponent

```
HTML + JS + CSS を１つのファイル（*.vue)
<templete> よく使うディレクティブ
v-if, v-else, v-else-if
v-for (v-for in), v-show
v-text, v-html
v-on (省略形は@) イベント
v-bind (省略形は:) 紐付け
v-model フォーム用
v-cloak
v-slot
トランジション関連
```

### Vue.js CompositionAPI(setup)

```
<script setup>
必要な機能はimportする
import { Link } from '@inertia/inertia-vue3' //
inertia側
import { ref } from 'vue' // vue側

コントローラからの受け渡し
defineProps({ id: String })

リアクティブな変数は refかreactiveで囲む
メソッドはJSを同じように書ける
```

### 関連マニュアル・講座

+ [Vue.js3 ドキュメント](https://v3.ja.vuejs.org/guide/introduction.html) <br>

+ [Vue.js3 APIリファレンス](https://v3.ja.vuejs.org/api/)<br>
