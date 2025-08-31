<template>
  <Link href="/">
    <img 
      src="/maguary.jpeg" 
      alt="Maguary"
      v-bind="$attrs"
    />
  </Link>
</template>

<script>
export default {
  inheritAttrs: false
}
</script>
