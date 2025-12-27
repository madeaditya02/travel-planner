<script setup>
import DatePicker from "primevue/datepicker";
import Dialog from "primevue/dialog";
import PlusButton from "@/Components/PlusButton.vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Button from "primevue/button";
import moment from "moment";

defineProps(['show', 'user']);
const show = defineModel('show')

const rangePlan = ref([new Date(), '']);
const formData = ref({
  name: '',
  startDate: new Date(),
  endDate: new Date(),
})
const errorForm = ref()
watch(show, s => {
  rangePlan.value = ['', '']
})

const loading = ref(false)
function createPlan(form, userId) {
  form.userId = userId;
  errorForm.value = null
  loading.value = true
  console.log(form);
  axios.post('/dashboard/plan', {
    ...form,
    startDate: moment(form.startDate).format("YYYY-MM-DD"),
    endDate: moment(form.endDate).format("YYYY-MM-DD")
  }).then(function (response) {
    // show.value = false;
    router.visit(`/dashboard/plans/${response.data.public_id}`)
  })
    .catch(function (error) {
      errorForm.value = error.response.data.message
      loading.value = false
      console.log(error);
    });
}
</script>
<template>
  <Dialog v-model:visible="show" modal :style="{ width: '32rem' }">
    <template #container="{ closeCallback }">
      <div class="px-7 py-5 new-plan-dialog">
        <div class="flex justify-between gap-8">
          <input type="text" placeholder="Plan name"
            class="plain-input block w-full p-0 border-none text-2xl focus:outline-none focus:border-none active:border-none active:outline-none"
            v-model="formData.name">
          <button @click="closeCallback">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="flex gap-2.5 items-center mt-5">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
          </svg>
          <DatePicker v-model="formData.startDate" :min-date="(new Date())" :manual-input="false"
            placeholder="mm/dd/yyyy">
          </DatePicker>
          <span>-</span>
          <DatePicker v-model="formData.endDate" :min-date="formData.startDate" :manual-input="false"
            placeholder="mm/dd/yyyy">
          </DatePicker>
          <input type="hidden" v-model="formData.userId">
        </div>
        <p class="text-sm text-red-600 mt-2" v-if="errorForm">Error: {{ errorForm }}</p>
        <Button :disabled="loading" @click="createPlan(formData, $page.props.auth.user.id)" class="mt-5">
          <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6 inline">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <svg v-if="loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6 inline animate-spin">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Add Plan
        </Button>
      </div>
    </template>
  </Dialog>
</template>
<style>
.plain-input,
.plain-input:focus {
  outline: none !important;
  border: none !important;
  box-shadow: none !important;
  /* Pastikan box-shadow juga dihapus */
}
</style>