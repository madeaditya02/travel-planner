<script setup>
defineProps(['rate'])
</script>
<template>
  <div class="flex items-center gap-1">
    <svg v-for="i in 5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
      stroke="#eab308" class="size-[19px]">
      <defs>
        <linearGradient id="grad">
          <stop offset="50%" stop-color="#eab308" />
          <stop offset="50%" stop-color="white" />
        </linearGradient>
      </defs>
      <path :fill="rate - i >= 0 ? '#eab308' : (rate - i >= -0.5 ? 'url(#grad)' : 'white')" stroke-linecap="round"
        stroke-linejoin="round"
        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
    </svg>
  </div>
</template>