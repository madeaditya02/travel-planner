<script setup>
import IconButton from '@/Components/IconButton.vue';
import OutlineButton from '@/Components/OutlineButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;

onMounted(() => {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 0) {
      document.querySelector('.navbar')?.classList.add('!shadow-lg')
    } else {
      document.querySelector('.navbar')?.classList.remove('!shadow-lg')
    }
  })
})

const toast = useToast();
onUnmounted(
  router.on('success', (event) => {
    const page = usePage();
    const alert = page.props.alert
    if (alert) {
      toast.add({ severity: alert[0], summary: alert[1], detail: alert[2], life: 4000 });
    }
  })
)

const show = ref(false)
</script>
<template>
  <nav
    class="navbar flex justify-between md:items-center md:px-[120px] py-6 flex-wrap flex-col md:flex-row fixed top-0 left-0 right-0 bg-white transition-all z-20">
    <div class="w-full md:w-auto flex justify-between items-center px-8 md:px-0">
      <Link href="/" class="text-3xl font-semibold">
        <img src="/img/Logo.png" alt="" class="w-32 sm:w-40 -mt-2">
      </Link>
      <IconButton class="md:hidden" @click="show = !show">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </IconButton>
    </div>
    <div class="md:!flex gap-4 md:gap-12 md:items-center flex-col md:flex-row py-5 md:py-0 px-8 md:px-0"
      :class="show ? 'flex' : 'hidden'">
      <Link href="/">Home</Link>
      <Link href="/stories">Stories</Link>
      <Link href="/explore">Explore</Link>
    </div>
    <div class="px-8 md:px-0 md:!block" :class="show ? 'block' : 'hidden'">
      <OutlineButton as-link="/dashboard" v-if="user">Dashboard</OutlineButton>
      <div class="flex items-center gap-5" v-if="!user">
        <OutlineButton as-link="/login">Log In</OutlineButton>
        <PrimaryButton as-link="/register">Sign Up</PrimaryButton>
      </div>
    </div>
  </nav>
  <div class="pt-20">
    <slot />
  </div>
  <Toast />
</template>