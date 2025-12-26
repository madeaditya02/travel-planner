<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputGroup from 'primevue/inputgroup';
import InputText from 'primevue/inputtext';
import Rating from 'primevue/rating';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';
import { reactive, ref, watch } from 'vue';
import Stars from './Stars.vue';
import axios from 'axios';
import { placePhoto } from '@/util';
import PrimaryButton from './PrimaryButton.vue';
import PlaceCard from './PlaceCard.vue';
// import { GoogleGenerativeAI } from '@google/generative-ai';
import { GoogleGenAI } from "@google/genai";
import Textarea from 'primevue/textarea';
import Loading from './Loading.vue';

defineProps(['visible'])
const emit = defineEmits(['selected'])
const show = defineModel()
const option = ref('Search')

const searchQuery = ref('')
const searchResults = ref([])
const loading = ref(false)
async function searchPlace(event) {
  if (loading.value)
    return
  searchResults.value = []
  loading.value = true
  searchResults.value = (await axios.post('https://places.googleapis.com/v1/places:searchText', {
    textQuery: searchQuery.value,
  }, {
    headers: {
      "X-Goog-Api-Key": import.meta.env.VITE_MAPS_API_KEY,
      "X-Goog-FieldMask": "*"
    }
  })).data.places
  loading.value = false
  console.log(searchResults.value);
}
const selectedPlace = ref({})
function submit() {
  emit('selected', selectedPlace.value)
  selectedPlace.value = {}
  searchQuery.value = ''
  searchResults.value = []
  show.value = false
}

// AI
const prompt = ref('')
const AISearchResults = ref([])
const loadingAI = ref(false)
// const genAI = new GoogleGenerativeAI(import.meta.env.VITE_GEMINI_API_KEY)
const genAI = new GoogleGenAI({ apiKey: import.meta.env.VITE_GEMINI_API_KEY })
// const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash", generationConfig: { responseMimeType: "application/json" } });
async function getAI() {
  if (loadingAI.value)
    return
  AISearchResults.value = []
  loadingAI.value = true
  const ourPrompt = `${prompt.value}. Provide some places (more than one) recommendations based on the prompt. Return an array of the fullname, city and regency of the place as one string.`;
  const result = await genAI.models.generateContent({
    model: "gemini-2.5-flash",
    contents: ourPrompt,
    config: {
      responseMimeType: "application/json",
    }
  });
  // const result = await model.generateContent(ourPrompt);
  // const response = await result.response;
  const text = response.text;
  const places = JSON.parse(text);
  places.forEach(async place => {
    const res = (await axios.post('https://places.googleapis.com/v1/places:searchText', {
      textQuery: place,
    }, {
      headers: {
        "X-Goog-Api-Key": import.meta.env.VITE_MAPS_API_KEY,
        "X-Goog-FieldMask": "*"
      }
    }))
    AISearchResults.value.push(res.data.places[0]);
    // return res.data.places[0]
  });
  loadingAI.value = false
}
</script>
<template>
  <Dialog v-model:visible="show" modal header="Select Location" :style="{ width: '36rem' }">
    <div>
      <Tabs value="0">
        <TabList>
          <Tab value="0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-5 inline mr-2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            Search
          </Tab>
          <Tab value="1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-5 inline mr-2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
            </svg>
            Recommendations
          </Tab>
        </TabList>
        <TabPanels>
          <TabPanel value="0">
            <form action="" class="mb-3" @submit.prevent="searchPlace">
              <InputGroup>
                <InputText placeholder="Type name of place" v-model="searchQuery" :readonly="loading" />
                <Button type="submit">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-5 md:hidden">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                  </svg>
                  <span class="hidden md:inline">Search</span>
                </Button>
              </InputGroup>
            </form>
            <PlaceCard v-for="place in searchResults" :place="place" @click="() => selectedPlace = place"
              class="hover:bg-gray-100 cursor-pointer active:bg-gray-200" :key="place.id"
              :class="selectedPlace.id == place.id ? 'bg-gray-200' : 'bg-white'" />
            <Loading v-if="loading" />
            <!-- <div class="py-4 border-b flex gap-4 hover:bg-gray-100 cursor-pointer" v-for="place in searchResults"
              @click="selectedPlace = place" :class="selectedPlace.id == place.id ? 'bg-gray-100' : 'bg-white'">
              <img :src="placePhoto(place.photos[0].name)" alt="" class="w-[160px] h-[120px] object-cover rounded-xl">
              <div class="flex-grow">
                <h3 class="text-xl font-semibold">{{ place.displayName.text }}</h3>
                <div class="flex items-center gap-2 mt-1" v-if="place.rating">
                  <span>{{ place.rating }}</span>
                  <Stars :rate="place.rating" />
                </div>
                <p class="mt-">{{ place.formattedAddress }}</p>
                <PrimaryButton :as-link="place.googleMapsUri" external class="!py-1 text-sm !rounded-md mt-1">View
                  Location
                </PrimaryButton>
              </div>
            </div> -->
          </TabPanel>
          <TabPanel value="1">
            <form action="" @submit.prevent="getAI">
              <Textarea name="" id="" v-model="prompt" placeholder="Describe what you want" class="block mb-3 w-full"
                rows="3" :readonly="loadingAI" />
              <PrimaryButton>Submit</PrimaryButton>
            </form>
            <PlaceCard v-for="place in AISearchResults" :place="place" @click="() => selectedPlace = place"
              class="hover:bg-gray-100 cursor-pointer" :key="place.id"
              :class="selectedPlace.id == place.id ? 'bg-gray-300' : 'bg-white'" />
            <Loading v-if="loadingAI" />
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>
    <template #footer>
      <Button :disabled="!Object.keys(selectedPlace).length" class="ml-[18px] mt-4" label="Select Location"
        @click="submit" />
    </template>
  </Dialog>
</template>
<style>
.p-tab {
  padding-top: 0;
  padding-bottom: 10px;
}

.p-tabpanels {
  padding-right: 0;
  padding-left: 0;
}
</style>