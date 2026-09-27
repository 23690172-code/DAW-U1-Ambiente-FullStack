<script setup>
defineProps({
  modelValue: String
})
defineEmits(['update:modelValue'])
</script>

<template>
  <label>
    Buscar servicio
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      type="search"
      placeholder="Ej. desarrollo web"
    />
  </label>
</template>