import { useStorage } from '@vueuse/core'
import { Dark } from 'quasar'

export const useAppShell = defineStore('app-shell', () => {
  const storageKey = ref('app-shell')

  const userId = usePage().props?.value?.auth?.user?.id

  if (userId) {
    storageKey.value = `${storageKey.value}-${userId}`
  }

  const defaultSettings = {
    isDark: false // Sempre inicia com tema claro
  }

  const currentSettings = useStorage(storageKey.value, defaultSettings)
  
  // Força tema claro sempre (ignora localStorage)
  currentSettings.value.isDark = false

  const setDarkMode = (value = null) => {
    if (value === null) {
      value = currentSettings.value.isDark
    }

    Dark.set(value)

    const body = document.querySelector('body');

    (value)
      ? body.classList.add('tw-dark')
      : body.classList.remove('tw-dark')
  }

  const toggleDarkMode = () => {
    currentSettings.value.isDark = !currentSettings.value.isDark
    setDarkMode()
  }

  return {
    setDarkMode,
    toggleDarkMode,
    settings: currentSettings
  }
})
