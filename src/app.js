import { createApp } from 'vue';

const app = createApp({});

app.component('mailtrapper',require('./components/Mailtrapper.vue').default);

app.mount('#mailtrapper_widget');