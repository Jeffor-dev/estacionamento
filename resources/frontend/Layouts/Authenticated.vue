<template>
  <q-layout view="lhr lpr lfr">
    <q-header>
      <q-toolbar class="tw-bg-primary-800">
        <q-btn
          dense
          flat
          round
          @click="
            ($q.screen.xs)
              ? isNavDrawerOpen = !isNavDrawerOpen
              : layout.navigationDrawer.mini = !layout.navigationDrawer.mini
          "
        >
          <i-mdi-menu class="tw-text-lg" />
        </q-btn>
        <q-toolbar-title class="tw-flex tw-items-center">
          <span class="tw-font-extralight">{{ $page.props.title }}</span>
        </q-toolbar-title>
        <q-space class="tw-hidden sm:tw-flex" />
        <AccountMenu />
      </q-toolbar>
    </q-header>

    <NavDrawer
      v-model:is-open="isNavDrawerOpen"
      v-model:mini="layout.navigationDrawer.mini"
    >
      <NavList bookmarker>
        <NavLink
          :href="route('dashboard')"
          :active="route().current('dashboard')"
        >
          <template #icon>
            <i-mdi-home-outline />
          </template>
          Dashboard
        </NavLink>
        <q-separator />
        <NavLink
          :href="route('estacionamento.index')"
          :active="route().current('estacionamento.index')"
        >
          <template #icon>
            <i-mdi-road-variant />
          </template>
          Estacionamento
        </NavLink>
        <NavLink
          :href="route('motorista.index')"
          :active="route().current('motorista.index')"
        >
          <template #icon>
            <i-mdi-person-outline />
          </template>
          Motorista
        </NavLink>
        <NavLink
          :href="route('caminhoes.index')"
          :active="route().current('caminhoes.index')"
        >
          <template #icon>
            <i-mdi-truck-outline />
          </template>
          Caminhões
        </NavLink>
        <NavLink
          :href="route('account.index')"
          :active="route().current('account.index')"
        >
          <template #icon>
            <i-mdi-settings-outline />
          </template>
          Conta
        </NavLink>
      </NavList>
    </NavDrawer>

    <q-page-container>
      <FadeTransition>
        <slot />
      </FadeTransition>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { useLocalStorage } from '@vueuse/core'

const $q = useQuasar()

const isNavDrawerOpen = ref($q.screen.gt.xs)

const layout = useLocalStorage(`authenticated-layout-${usePage().props.auth.user.id}`, {
  navigationDrawer: {
    mini: true
  }
})
</script>
