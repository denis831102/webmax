<!-- <template>
  <div> -->
<!-- <h1 v-if="level === 1">
      <slot></slot>
    </h1>
    <h2 v-else-if="level === 2">
      <slot></slot>
    </h2>
    <h3 v-else-if="level === 3">
      <slot></slot>
    </h3>
    <h4 v-else-if="level === 4">
      <slot></slot>
    </h4>
    <h5 v-else-if="level === 5">
      <slot></slot>
    </h5>
    <h6 v-else-if="level === 6">
      <slot></slot>
    </h6> -->
<!-- </div>
</template> -->

<script>
import { h /* resolveComponent */ } from "vue";

export default {
  name: "my-heading",
  props: {
    level: {
      type: Number,
      required: true,
    },
  },
  // methods: {
  //   MyComponent() {
  //     this.router.push({ name: "profile" });
  //   },
  // },
  render() {
    return [
      h(
        "h4",
        { style: "margin:10px 20px; font-style: italic;" },
        "комбінований сервіс"
      ),
      h(
        "h" + this.level,
        // resolveComponent("my-button"),
        {
          name: "my-head",
          onClick: () => this.$router.push({ name: "profile" }),
          style: "margin: 0 20px",
        },
        this.$slots.default()
      ),
    ];
  },
};
</script>

<style></style>
