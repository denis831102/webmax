<template>
  <select :value="modelValue" @change="changeOption" class="sel">
    <option value="" disabled>Оберіть зі списку</option>
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.name }}
    </option>
  </select>
</template>

<script>
export default {
  name: "my-select",
  props: {
    modelValue: {
      type: String,
    },
    options: {
      type: Array,
      default: () => [],
    },
    // model:{
    //   prop:"value",
    //   event: "change"
    // }
  },
  methods: {
    changeOption(event) {
      this.$emit("update:modelValue", event.target.value);
    },
  },
};
</script>

<style>
.sel {
  min-height: 20px;
  margin: 15px 0;
  border: 1px solid teal;
  color: teal;
  padding: 0 10px;
}
</style>
