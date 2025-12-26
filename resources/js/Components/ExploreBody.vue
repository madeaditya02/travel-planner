<script setup>
import DestinationCard from "@/Components/DestinationCard.vue";
import DestinationTypeIcon from "@/Components/DestinationTypeIcon.vue";
import ExistingPlanModal from "@/Components/ExistingPlanModal.vue";
import FeaturedPlaces from "@/Components/FeaturedPlaces.vue";
import Loading from "@/Components/Loading.vue";
import NewPlanModal from "@/Components/NewPlanModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import { GoogleGenerativeAI } from "@google/generative-ai";
import { GoogleGenAI } from "@google/genai";
import axios from "axios";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import InputGroup from "primevue/inputgroup";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import Select from "primevue/select";
import Tab from "primevue/tab";
import TabList from "primevue/tablist";
import TabPanel from "primevue/tabpanel";
import TabPanels from "primevue/tabpanels";
import Tabs from "primevue/tabs";
import Textarea from "primevue/textarea";
import { onMounted, ref, watch } from "vue";
import NewPlanActivityModal from "./NewPlanActivityModal.vue";

const { provinces } = defineProps(['provinces', 'plans'])

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

const search = ref('');
const loadingSearch = ref(false);
const placesSearch = ref([])
async function submitSearch() {
  loadingSearch.value = true
  try {
    placesSearch.value = (await axios.post('https://places.googleapis.com/v1/places:searchText', {
      textQuery: search.value,
    }, {
      headers: {
        "X-Goog-Api-Key": import.meta.env.VITE_MAPS_API_KEY,
        "X-Goog-FieldMask": "*"
      }
    })).data.places
    loadingSearch.value = false
  } catch (error) {
    console.error(error);
    loadingSearch.value = false
  }
}

// AI Recommendations
const prompt = ref('')
const AISearchResults = ref([])
const loadingAI = ref(false)
// const genAI = new GoogleGenerativeAI(import.meta.env.VITE_GEMINI_API_KEY)
const genAI = new GoogleGenAI({ apiKey: import.meta.env.VITE_GEMINI_API_KEY })
// const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash", generationConfig: { responseMimeType: "application/json" } });
async function getAI() {
  console.log(prompt.value);
  if (loadingAI.value)
    return
  AISearchResults.value = []
  loadingAI.value = true
  const ourPrompt = `${prompt.value}. Provide some places (more than one) recommendations based on the prompt. Return an array of the fullname, city and regency of the place as one string.`;
  // const result = await model.generateContent(ourPrompt);
  const result = await genAI.models.generateContent({
    model: "gemini-2.5-flash",
    contents: ourPrompt,
    config: {
      responseMimeType: "application/json",
    }
  });
  // const response = await result.response;
  const text = result.text;
  const places = JSON.parse(text);
  console.log(places);
  places.forEach(async place => {
    // console.log(place);
    try {
      const res = (await axios.post('https://places.googleapis.com/v1/places:searchText', {
        textQuery: place,
      }, {
        headers: {
          "X-Goog-Api-Key": import.meta.env.VITE_MAPS_API_KEY,
          "X-Goog-FieldMask": "*"
        }
      }))
      console.log(res);
      AISearchResults.value.push(res.data.places[0]);
    } catch (error) {
      console.error(error);
      loadingAI.value = false
    }
    // return res.data.places[0]
  });
  loadingAI.value = false
}
</script>
<template>
  <h1 class="text-3xl font-semibold mb-5">Explore Indonesia</h1>

  <Tabs value="0">
    <TabList>
      <Tab value="0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-5 inline mr-1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
        </svg>
        Featured
      </Tab>
      <Tab value="1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-5 inline mr-1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
        Recommendations
      </Tab>
      <Tab value="2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-5 inline mr-1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        Search
      </Tab>
    </TabList>
    <TabPanels>
      <TabPanel value="0">
        <FeaturedPlaces :provinces="provinces" :plans="plans" />
      </TabPanel>
      <TabPanel value="1">
        <form action="" @submit.prevent="getAI">
          <Textarea name="" id="" v-model="prompt" placeholder="Describe what you want" class="block mb-3 w-full"
            rows="3" />
          <PrimaryButton>Search</PrimaryButton>
        </form>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <DestinationCard v-for="place in AISearchResults" :place="place" @show-modal="selectPlaceExisting(place)"
            @show-new-plan-modal="selectPlaceNew(place)" />
        </div>
        <Loading v-if="loadingAI" />
      </TabPanel>
      <TabPanel value="2">
        <form action="" class="mb-3 w-[30rem] max-w-full" @submit.prevent="submitSearch">
          <InputGroup>
            <InputText placeholder="Type name of place" v-model="search" />
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
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <DestinationCard v-for="place in placesSearch" :place="place" @show-modal="selectPlaceExisting(place)"
            @show-new-plan-modal="selectPlaceNew(place)" />
        </div>
        <Loading v-if="loadingSearch" />
      </TabPanel>
    </TabPanels>
  </Tabs>

  <ExistingPlanModal v-model:show="showModal" :plans="plans" :selected-place="selectedPlace" />
  <NewPlanActivityModal v-model:show="showNewPlanModal" :selected-place="selectedPlace" />
</template>