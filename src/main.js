import { createApp } from "vue";
import App from "./App";
//import App from "./views/VPage1";
import router from "./router";
import store from "./store";

import ElementPlus from "element-plus";
import * as ElementPlusIconsVue from "@element-plus/icons-vue";

import componentsUI from "@/components/UI";

//import VIntersection from "./directives/VIntersection";
import directives from "./directives";

import pluginWord from "@/plugins/pluginWord";
import { dictonary } from "@/plugins/dictonary";

const app = createApp(App);

componentsUI.forEach((component) => {
  app.component(component.name, component);
});

// app.directive("intersection", VIntersection);
directives.forEach((directive) => {
  app.directive(directive.name, directive);
});

for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component);
}

app.use(ElementPlus);

app.use(pluginWord, dictonary).use(router).use(store).mount("#app");
// app.use(router).use(store).mount("#app");
// app.use(pluginW, dictonary).mount("#app");

//console.log(app);
