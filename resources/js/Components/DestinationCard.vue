<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { placePhoto } from "@/util";
import { usePage } from "@inertiajs/vue3";
import Popover from "primevue/popover";
import { ref } from "vue";
const { place } = defineProps(['place'])
const { props: { auth: { user: loggedUser } } } = usePage()
const menuAddPlan = ref();
const emit = defineEmits(['showModal', 'showNewPlanModal']);
function toggleAddPlan(event) {
  menuAddPlan.value.toggle(event);
}
function emitShowModal(event) {
  emit('showModal');
}
function emitShowNewPlanModal(event) {
  emit('showNewPlanModal');
}
const address = place?.addressComponents.filter(comp => comp.types?.includes('administrative_area_level_3') || comp.types?.includes('administrative_area_level_1')).map(comp => comp.shortText).join(', ')
const showDesc = ref(false)
</script>
<template>
  <div class="w-full rounded-xl border px-6 py-5 flex flex-col justify-between">
    <div>
      <img v-if="place?.photos" :src="placePhoto(place?.photos[0].name)" alt=""
        class="w-full h-[160px] object-cover rounded-xl">
      <h2 class="text-2xl font-semibold mt-4">{{ place?.displayName.text }}</h2>
      <div class="mb-3 mt-2 flex flex-col gap-3">
        <div class="flex gap-2.5 items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
          <div class="text-wrap">{{ address }}</div>
        </div>
        <!-- <div class="flex gap-2.5 items-center" v-if="place?.rating">
          <svg xmlns="http://www.w3.org/2000/svg" fill="#FAF35E" viewBox="0 0 24 24" stroke-width="1.5" stroke="#FAF35E"
            class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
          </svg>
          <span>{{ place?.rating }}</span>
        </div> -->
      </div>
      <p>{{ place?.short_description }}</p>
    </div>
    <div class="mt-2 flex items-center gap-3">
      <!-- <button class="px-3 py-1.5 rounded-lg bg-blue-primary text-white">More Info</button> -->
      <PrimaryButton :as-link="place?.googleMapsUri" external class="px-3 !py-1.5">More Info</PrimaryButton>
      <PrimaryButton v-if="loggedUser" class="px-3 !py-1.5" @click="toggleAddPlan">Add to Plan</PrimaryButton>
      <Popover v-if="loggedUser" ref="menuAddPlan">
        <div class="flex flex-col">
          <a href="#" class="px-3 py-1 rounded-md hover:bg-gray-100" @click="emitShowModal">Add to Existing Plan</a>
          <a href="#" class="px-3 py-1 rounded-md hover:bg-gray-100" @click="emitShowNewPlanModal">Create New Plan</a>
        </div>
      </Popover>
    </div>
  </div>
</template>