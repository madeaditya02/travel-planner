<script setup>
import DestinationCard from "@/Components/DestinationCard.vue";
import ExistingPlanModal from "@/Components/ExistingPlanModal.vue";
import Loading from "@/Components/Loading.vue";
import NewPlanModal from "@/Components/NewPlanModal.vue";
import { GoogleGenAI } from "@google/genai";
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { z } from "zod";

const { provinces, count, countMany, plans } = defineProps(['provinces', 'count', 'countMany', 'plans'])
const showModal = ref(false);
const showNewPlanModal = ref(false);
const selectedPlace = ref(null)

function selectPlaceExisting(place) {
  showModal.value = true
  selectedPlace.value = place
}
function selectPlaceNew(place) {
  showNewPlanModal.value = true
  selectedPlace.value = place
}

watch(showModal, val => !val ? selectedPlace.value = null : null)
watch(showNewPlanModal, val => !val ? selectedPlace.value = null : null)

const genAI = new GoogleGenAI({ apiKey: import.meta.env.VITE_GEMINI_API_KEY })
// const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash", generationConfig: { responseMimeType: "application/json" } });

const featuredPlacesName = ref([])
const featuredPlaces = ref([])
const loading = ref(false)
const currentIndex = ref(0)
async function loadMorePlaces() {
  if (currentIndex.value >= featuredPlacesName.value.length) return
  console.log('Load more');
  loading.value = true
  const loadedNames = featuredPlacesName.value.slice(currentIndex.value, currentIndex.value + 6)
  loadedNames.forEach(async place => {
    const res = (await axios.post('https://places.googleapis.com/v1/places:searchText', {
      textQuery: place.fullname,
    }, {
      headers: {
        "X-Goog-Api-Key": import.meta.env.VITE_MAPS_API_KEY,
        "X-Goog-FieldMask": "*"
      }
    }))
    featuredPlaces.value.push({ ...res.data.places[0], short_description: place.short_description })
    console.log(res);
  })
  currentIndex.value += 6
  loading.value = false
}

onMounted(async () => {
  loading.value = true
  const ourPrompt = `${provinces?.length > 1 ? (count ?? 20) : (countMany ?? 40)}+ places hidden gem in ${provinces?.length ? provinces.join(', ') : 'Indonesia'}. Return array of object {fullname, short_description}`;
  // const result = await model.generateContent(ourPrompt);
  const result = await genAI.models.generateContent({
    model: "gemini-2.5-flash",
    contents: ourPrompt,
    config: {
      responseMimeType: "application/json",
    }
  })
  // const outputSchema = z.array(z.object({

  // }))
  // for await (const chunk of result) {
  //   console.log(chunk.text)
  // }
  // const response = await result.te;
  const text = JSON.parse(result.text);
  featuredPlacesName.value = text
  console.log("Hasil AI", text);
  await loadMorePlaces()
  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting && !loading.value) loadMorePlaces()
  }), {
    rootMargin: "-150px 0px 0px 0px"
  })
  observer.observe(document.querySelector('.loadMoreIntersect'))
})
</script>
<template>
  <div v-if="featuredPlaces.length > 0" class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <DestinationCard v-for="place in featuredPlaces" :place="place" @show-modal="selectPlaceExisting(place)"
      @show-new-plan-modal="selectPlaceNew(place)" />
  </div>
  <Loading v-if="loading" />
  <span class="loadMoreIntersect"></span>

  <ExistingPlanModal v-model:show="showModal" :plans="plans" :selected-place="selectedPlace" />
  <NewPlanModal v-model:show="showNewPlanModal" :selected-place="selectedPlace" />
</template>