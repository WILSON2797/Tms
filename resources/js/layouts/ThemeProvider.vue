<template>
  <slot></slot>
</template>

<script setup>
import { ref, provide, onMounted, watch, computed } from 'vue'

const theme = ref('light')
const isInitialized = ref(true)

const isDarkMode = computed(() => false)

const toggleTheme = () => {
  // Disabled theme toggling
}

onMounted(() => {
  theme.value = 'light'
  document.documentElement.classList.remove('dark')
})

watch([theme, isInitialized], () => {
  document.documentElement.classList.remove('dark')
  localStorage.setItem('theme', 'light')
})

provide('theme', {
  isDarkMode,
  toggleTheme,
})
</script>
