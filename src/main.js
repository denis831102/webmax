/* eslint-disable */

import { createApp } from "vue";

// import Vue from "vue";
import BootstrapVue from "bootstrap-vue";
import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";

import App from "./App";
//import App from "./views/VPage1";
import router from "./router";
import store from "./store";

import ElementPlus from "element-plus";
import * as ElementPlusIconsVue from "@element-plus/icons-vue";

import componentsUI from "@/components/UI";

//import VIntersection from "./directives/VIntersection";
import directives from "./directives";

import pluginSystem from "@/plugins/pluginSystem";
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

app
  .use(pluginSystem, dictonary)
  .use(router)
  .use(store)
  .use(ElementPlus)
  .use(BootstrapVue)
  .mount("#app");
