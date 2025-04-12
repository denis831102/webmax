<template>
  <input type="text" class="input" />
</template>

<script>
export default {
  name: "my-input-comp",
  props: {
    modelValue: [String, Number],
  },
  computed: {
    value: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit("update:modelValue"), value;
      },
    },
  },
};
</script>

<style scoped>
.input {
  width: 100%;
  border: 1px solid teal;
  padding: 10px 15px;
  margin-top: 15px;
}
</style>
