import Vue from 'vue';

import '@/extensions';

import ApiService from '@/services/api.service';
import { router } from './router';
import store from './store';
import i18n from './i18n';

import App from './App.vue';

import '@/plugins';
import '@/components';

import '@/styles/main.scss';

ApiService.init(process.env.VUE_APP_ENDPOINT_URL);

/*
  <script data-jsd-embedded
          data-key="66574d23-7124-49b1-acf6-17f72bfd4878"
          data-base-url="https://jsd-widget.atlassian.com"
          src="https://jsd-widget.atlassian.com/assets/embed.js"></script>

 */
const atlassianApiKey = process.env.VUE_APP_ATLASSIAN_API_KEY;
if (atlassianApiKey) {
  const widgetScriptLink = document.createElement('script');
  widgetScriptLink.setAttribute('data-jsd-embedded', '');
  widgetScriptLink.setAttribute('data-base-url', 'https://jsd-widget.atlassian.com');
  widgetScriptLink.setAttribute('data-key', atlassianApiKey);
  widgetScriptLink.setAttribute('src', 'https://jsd-widget.atlassian.com/assets/embed.js');
  document.querySelector('body').append(widgetScriptLink);
}

if (process.env.VUE_APP_ZAMMAD_CHAT_ENABLED === 'true') {
  const zammadChatScriptLink = document.createElement('script');
  zammadChatScriptLink.setAttribute('src', 'https://zammad.cultuzz.com/assets/chat/chat-no-jquery.min.js');
  document.querySelector('body').append(zammadChatScriptLink);
}


Vue.config.productionTip = false;

new Vue({
  i18n,
  router,
  store,
  render: (h) => h(App, { ref: 'App' }),
}).$mount('#app');
