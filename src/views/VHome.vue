<template>
  <!-- eslint-disable  -->
  <div class="cMain">
    <RouterLink
      v-for="(option, ind) in options"
      :key="ind"
      :to="option.link"
      title="option.title.join('\n')"
      class="router"
    >
      <my-button
        :class="{ link: isPar(ind), 'link-down': !isPar(ind) }"
        @mousemove="changeInd(ind)"
      >
        {{ option.name }}
        <template #adslot>{{ String.fromCodePoint(option.symbol) }}</template>
      </my-button>
    </RouterLink>

    <div class="text" v-html="curTitle"></div>
    <!-- <div class="text">{{ curTitle }}</div> -->

    <div class="footer">{{ kafedra }}</div>
  </div>
</template>

<script>
export default {
  name: "MainPage",
  data() {
    return {
      curInd: 0,
      kafedra: "ІТП 2025",
      options: [
        {
          link: "/page1",
          title: [
            "створення single files components",
            "двонапрвлений зв'язок",
            "використання Vue Router (маршрутизація)",
            "кастомний модифікатор v-model",
            "використання плагіна",
          ],
          name: "Сервіс v1",
          symbol: 128214,
        },
        {
          link: "/page2",
          title: [
            "додане завантаження з серверу",
            "видалення посту",
            "додане вікно dialog (задіяний teleport)",
            "додане сортування",
            "доданий пошук",
            "створена пагинация",
            "додана анімація списку постів",
          ],
          name: "Сервіс v2 (dialog, pagination)",
          symbol: 128215,
        },
        {
          link: "/page3",
          title: [
            "доданий Intersection Observer API (замість пагінації)",
            "застосований сторонній SFC Loader",
            "використаний Promise при завантаженні (затримка 2с.)",
            "використання бібліотеки Lodash для debounce (дребезга)",
            "задіяний модификатор клавіш",
            "у компонента задіяна Render функція",
            "додана анімація loader",
            "computed в компоненті",
          ],
          name: "Сервіс v3 (new Intersection)",
          symbol: 128216,
        },
        {
          link: "/page4",
          title: [
            "додана директива v-Intersection",
            "додана директива v-focus",
            "відкриття окремого посту (динамічна навігація)",
            "створення mixins для вікна діалога",
            "задіяні provide, inject",
          ],
          name: "Сервіс v4 (directives, mixins)",
          symbol: 128217,
        },
        {
          link: "/page5",
          title: [
            "Vuex (глобальний store)",
            "додана форма лайків (з динамічним аргументом)",
          ],
          name: "Сервіс v5 (Vuex)",
          symbol: 128218,
        },
        {
          link: "/page6",
          title: [
            "Composition API",
            "компонент пагінації",
            "пошук з кольровим маркером",
          ],
          name: "Сервіс v6 (Composition API)",
          symbol: 128219,
        },
        {
          link: "/page7",
          title: ["Бібліотека Prime Vue"],
          name: "Сервіс v7",
          symbol: 128220,
        },
        {
          link: "/page8",
          title: ["Бібліотека Element Plus"],
          name: "Сервіс v8",
          symbol: 128221,
        },
      ],
    };
  },
  methods: {
    isPar(ind) {
      return ind % 2;
    },
    changeInd(ind) {
      this.curInd = ind;
    },
  },
  computed: {
    curTitle() {
      return [
        "<h3>",
        this.options[this.curInd].name,
        "</h3>",
        "<BR>",
        "<ol>",
        ...this.options[this.curInd].title.map((el) => `<li>${el}</li>`),
        "</ol>",
        // ...this.options[this.curInd].title,
      ].join("");
    },
  },
};
</script>

<style scoped>
.cMain {
  display: flex;
  /* align-items: self-end; */
  margin-left: auto;
  /* flex-direction: column; */
  justify-content: center;
  flex-flow: wrap;
  padding: 20px 5%;
}
.router {
  /* display: flex; */
  flex-direction: column;
}
.link:hover {
  transform: scale(1.2);
}
.link-down:hover {
  border: 5px solid teal;
}
.footer {
  bottom: 12px;
  position: absolute;
  color: teal;
}
.text {
  color: teal;
  border: 1px solid teal;
  justify-content: center;
  /* position: absolute; */
  width: 50%;
  top: 25%;
  padding: 20px;
}
</style>
