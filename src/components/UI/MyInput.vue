<template>
  <input :value="modelValue" @input="updateInput" type="text" class="input" />

  <!-- <input
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    type="text"
    class="input"
  /> -->
</template>

<script>
export default {
  name: "my-input",
  props: {
    modelValue: [String, Number],
    modelModifiers: {
      default: () => ({}),
    },
  },
  methods: {
    updateInput(event) {
      let val = event.target.value;
      if (this.modelModifiers.capitalize) {
        val = val.charAt(0).toUpperCase() + val.slice(1);
      }
      this.$emit("update:modelValue", val);
    },
  },
};
</script>

<style scoped>
.input {
  width: 80%;
  border: 1px solid teal;
  padding: 10px 15px;
  margin-top: 15px;
}
</style>
