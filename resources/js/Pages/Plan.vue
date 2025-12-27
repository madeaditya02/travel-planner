<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import ActivityCard from '@/Components/ActivityCard.vue';
import DeletePlanButton from '@/Components/DeletePlanButton.vue';
import PencilSquareIconButton from '@/Components/PencilSquareIconButton.vue';
import MapComponent from '@/Components/MapComponent.vue';
import PlaceCard from '@/Components/PlaceCard.vue';
import PlusButton from '@/Components/PlusButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectLocation from '@/Components/SelectLocation.vue';
import ShareDialog from '@/Components/ShareDialog.vue';
import EditPlanModal from '@/Components/EditPlanModal.vue';
import ShareIconButton from '@/Components/ShareIconButton.vue';
import Trivia from '@/Components/Trivia.vue';
import ExtendPlanDialog from '@/Components/ExtendPlanDialog.vue';
import ActivityEditModal from '@/Components/ActivityEditModal.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { planStatus, rangePlan } from '@/util';
import { useForm, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
import DatePicker from 'primevue/datepicker';
import Popover from 'primevue/popover';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import Button from 'primevue/button';
import OutlineButton from '@/Components/OutlineButton.vue';
import axios from 'axios';
import Toast from 'primevue/toast';

defineOptions({ layout: DashboardLayout });

const { props: { auth } } = usePage()
const props = defineProps(['plan']);
const plan = ref(props.plan);
const planActivity = ref([]);
const activeActivity = ref({});
const confirm = useConfirm();
const toast = useToast();

const getActivity = () => {
  return axios.get(`/dashboard/activities/${plan.value.public_id}`)
    .then((res) => {
      planActivity.value = res.data;
      plan.value.activities = res.data
    })
    .catch((err) => {
      console.log(err);
    })
}

const activities = computed(() => {
  return planActivity.value.filter(act => moment.utc(act.time).isSameOrAfter(moment()))
})
const completedActivities = computed(() => {
  return planActivity.value.filter(act => moment.utc(act.time).isBefore(moment()))
})
const rangeTime = computed(() => rangePlan(plan.value))

const onlineUsers = ref([])
onMounted(() => {
  getActivity();
  // window.Echo.join(`plan.${plan.value.id}`)
  //   .here((users) => {
  //     onlineUsers.value = users
  //   })
  //   .joining((user) => {
  //     onlineUsers.value.push(user)
  //   })
  //   .leaving((user) => {
  //     onlineUsers.value = onlineUsers.value.filter(u => u.id != user.id)
  //   })
  //   .listen('UpdateActivity', e => {
  //     // planActivity.value = e.activities
  //     getActivity();
  //     // console.log(e);
  //     // console.log('updated');
  //   })
  //   .error((error) => {
  //     console.error(error);
  //   });
})

onUnmounted(() => {
  // Echo.leave(`plan.${plan.value.id}`)
})

const showAddPlan = ref(false);
const showShare = ref(false);
const showExtend = ref(false);
const showEditPlan = ref(false);
const showEditActivity = ref(false);
const showMap = ref(true);

const planAction = ref()
function showPlanAction(event) {
  planAction.value.toggle(event)
}

const isOwner = computed(() => {
  return props.plan.users.filter(user => user.pivot.role == 'Owner')[0].id == auth.user.id
})


// Location
const selectLocation = ref(false)
function handleSelectedLocation(place) {
  // Untuk Add Activity
  if (showAddPlan) {
    newActivity.place = place
  }
}

// Add Activity
watch(showAddPlan, () => {
  newActivity.reset()
})
const newActivity = useForm({
  time: '',
  place: null
});
const loadingAdd = ref(false)
async function submitActivity() {
  loadingAdd.value = true
  const res = await axios.post(`/dashboard/plans/${plan.value.id}/activities`, {
    data: newActivity.data(),
    activities: plan.value.activities
  })
  getActivity()
  showAddPlan.value = false;
  newActivity.reset();
  loadingAdd.value = false
}

async function deleteActivity(id) {
  planActivity.value = planActivity.value.filter(act => act.id != id)
  const res = await axios.delete(`/dashboard/plans/${plan.value.id}/activities/${id}`, {
    data: {
      activities: plan.value.activities,
    }
  })
  getActivity()
}

async function deletePlan() {
  // axios.delete(`/dashboard/plan/${plan.value.id}`)
  //   .then(function () {
  //     router.visit('/dashboard/plans');
  //   })
  //   .catch(function (err) {
  //     console.log(err);
  //   })
  router.delete(`/dashboard/plan/${plan.value.id}`)
}

function deletePlanConfirm() {
  confirm.require({
    message: 'Do you want to delete this plan?',
    header: 'Danger Zone',
    icon: 'pi pi-info-circle',
    rejectLabel: 'Cancel',
    rejectProps: {
      label: 'Cancel',
      severity: 'secondary',
      outlined: true
    },
    acceptProps: {
      label: 'Delete',
      severity: 'danger'
    },
    accept: function () {
      toast.add({ severity: 'info', summary: 'Confirmed', detail: 'Plan deleted', life: 3000 });
      deletePlan();
    }
  })
}

async function editSubmitted() {
  showEditPlan.value = false
  plan.value = (await axios.get(`/dashboard/plans/${plan.value.public_id}?reload=plan`)).data.plan
  toast.add({ severity: 'success', summary: 'Edit Plan', detail: 'Plan has been dited', life: 4000 })
}

</script>
<template>
  <Toast />
  <div class="flex justify-between">
    <div>
      <h1 class="text-3xl font-semibold">{{ plan.name }}</h1>
      <div class="flex gap-3 items-center my-5">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
        </svg>
        <span>{{ rangeTime }}</span>
      </div>
      <div class="flex gap-3 items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <span>{{ plan.activities.length }} Activities, {{ activities.length }} Remains</span>
      </div>
    </div>
    <div class="flex items-end flex-col gap-4">
      <button @click="showPlanAction" v-if="isOwner" class="lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-7">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>
      </button>
      <Popover ref="planAction" v-if="isOwner && planStatus(plan) != 'Completed'">
        <div class="flex">
          <share-icon-button v-if="isOwner" @share="showShare = true" />
          <PencilSquareIconButton @click="showEditPlan = true" />
          <DeletePlanButton v-if="isOwner" @click="deletePlanConfirm()" />
        </div>
      </Popover>
      <div v-if="planStatus(plan) != 'Completed'" :class="isOwner ? 'hidden lg:flex gap-3' : ''">
        <share-icon-button v-if="isOwner" @share="showShare = true" />
        <PencilSquareIconButton @click="showEditPlan = true" />
        <DeletePlanButton v-if="isOwner" @click="deletePlanConfirm()" />
      </div>
      <OutlineButton v-if="planStatus(plan) == 'Completed'" :as-link="`/dashboard/plans/${plan.public_id}/story`">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
        </svg>
        Write Story
      </OutlineButton>
      <div v-if="planStatus(plan) != 'Completed'" class="hidden md:flex">
        <a href="#" @click="showShare = true">
          <img v-for="(user, i) in onlineUsers" :src="user.profile_picture" alt=""
            class="w-[48px] h-[48px] inline-block rounded-full relative last:!right-0 shadow-md"
            :style="{ right: `${((i + 1) - onlineUsers.length) * 8}px`, zIndex: (i + 1) * -1 }">
        </a>
      </div>
      <!-- <div>hello</div> -->
    </div>
  </div>
  <div class="mt-8 flex gap-8 flex-col md:flex-row">
    <div class="md:w-[60%]">
      <h2 class="text-2xl font-semibold mb-4">Activities</h2>
      <div class="flex justify-between">
        <PlusButton v-if="!showAddPlan" @click="showAddPlan = true">Add Activity</PlusButton>
        <!-- <button
          class="inline-flex px-3 py-2 rounded-lg gap-2.5 items-center bg-blue-primary text-white hover:bg-sky-600 active:bg-blue-700"
          @click="showExtend = true">
          Extend Plan
        </button> -->
      </div>
      <!-- Form Add Activity -->
      <form class="px-6 py-5 border rounded-xl mt-4" v-if="showAddPlan" @submit.prevent="submitActivity">
        <div class="flex gap-5 items-center">
          <div class="flex flex-col sm:flex-row gap-2.5"
            :class="newActivity.place ? 'sm:!flex-col w-full' : 'sm:items-center'">
            <div class="flex gap-2.5 items-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
              </svg>
              <button @click="selectLocation = true" type="button"
                class="px-3 py-1 rounded-md border border-[#cbd5e1] hover:border-[#94a3b8]">Select
                Location</button>
            </div>
            <PlaceCard v-if="newActivity.place" :place="newActivity.place" />
            <div class="flex gap-2.5 items-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              <DatePicker v-model="newActivity.time" showTime hourFormat="24" :min-date="new Date(plan.start_date)"
                :max-date="new Date(plan.end_date)" placeholder="mm/dd/yyyy --:--"></DatePicker>
            </div>
          </div>
        </div>
        <div class="flex gap-2.5 mt-3 items-center">
          <Button type="submit" :disabled="loadingAdd || !newActivity.time || !newActivity.place"
            class="!py-1 text-sm !rounded-md">
            <svg v-if="loadingAdd" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-5 inline animate-spin">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Add Activity
          </Button>
          <button type="reset" class="px-3 py-1 rounded-md border border-red-600 text-red-600 text-sm"
            @click="showAddPlan = false">Cancel</button>
        </div>
      </form>
      <div v-if="activities.length">
        <ActivityCard v-for="activity in activities" :key="activity.id" :activity="activity"
          @delete="deleteActivity(activity.id)"
          @select-edit="(activity) => { activeActivity = activity; showEditActivity = true; }" />
      </div>
      <hr class="mt-4">
      <ActivityCard v-for="activity in completedActivities" :key="activity.id" :activity="activity" />

    </div>
    <div class="md:w-[40%]">
      <h2 class="text-2xl font-semibold mb-4">Maps</h2>
      <MapComponent class="my-4" v-if="showMap" :places="plan.activities.map(act => act.place)" />
      <!-- <Trivia /> -->
    </div>
  </div>

  <ShareDialog v-model:visible="showShare" :plan="plan" :online-users="onlineUsers" />
  <ExtendPlanDialog v-model:visible="showExtend" :plan="plan" />
  <EditPlanModal v-model:visible="showEditPlan" :plan="plan" @submitted="editSubmitted" />
  <ActivityEditModal v-model:show="showEditActivity" :activity="activeActivity" :plan="plan"
    @submitted="getActivity();" />
  <SelectLocation v-model="selectLocation" @selected="handleSelectedLocation" />
</template>
<style>
.p-datepicker-input {
  padding: 4px 12px !important;
}
</style>