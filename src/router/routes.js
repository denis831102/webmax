export const routes = [
  {
    path: "/",
    name: "crm",
    component: () => import("@/views/VWorkSpace.vue"),
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: "/authent",
    name: "authent",
    component: () => import("@/views/VAuthend.vue"),
    // meta: {
    //   requiresAuth: false,
    // },
  },
  {
    path: "/block",
    name: "block",
    component: () => import("@/views/VBlock.vue"),
    // meta: {
    //   requiresAuth: false,
    // },
  },
  // {
  //   name: "profile",
  //   path: "/home",
  //   alias: "/home",
  //   component: () => import("@/views/VHome.vue"),
  // },
  // {
  //   name: "page1",
  //   path: "/page1",
  //   component: () => import("@/views/VPage1.vue"),
  // },
  // {
  //   name: "page2",
  //   path: "/page2",
  //   component: () => import("@/views/VPage2.vue"),
  // },
  // {
  //   name: "page3",
  //   path: "/page3",
  //   component: () => import("@/views/VPage3.vue"),
  // },
  // {
  //   name: "page4",
  //   path: "/page4",
  //   component: () => import("@/views/VPage4.vue"),
  // },
  // {
  //   name: "page4i",
  //   path: "/page4/:id",
  //   component: () => import("@/components/UI/PostIdPage.vue"),
  // },
  // {
  //   path: "/page5",
  //   component: () => import("@/views/VPage5.vue"),
  // },
  // {
  //   path: "/page6",
  //   component: () => import("@/views/VPage6.vue"),
  // },
];
