<script setup>
import axios from "axios";
import { ref, onMounted, defineProps } from "vue";
const onlineUsers = ref([])
const { plan } = defineProps(['plan'])
const activities = ref(plan.activities)
onMounted(() => {
  // console.log(plan);
  plan.activities = plan.activities.map(a => ({ ...a, plan_id: plan.id }))
  // Echo.join(`chat.${plan.id}`)
  //   .here((users) => {
  //     onlineUsers.value = users
  //   })
  //   .joining((user) => {
  //     console.log(user.name);
  //     onlineUsers.value.push(user)
  //   })
  //   .leaving((user) => {
  //     console.log(user.name);
  //     onlineUsers.value = onlineUsers.value.filter(u => u.id != user.id)
  //   })
  //   .listen('UpdateActivity', e => {
  //     // console.log('Hello');
  //     console.log(e);
  //     activities.value = e.activities
  //   })
  //   .error((error) => {
  //     console.error(error);
  //   });
  // Echo.channel(`activities.${plan.id}`).listen('UpdateActivity', e => {
  //  console.log("Hello");
  //  console.log(e);
  //})
})
const addActivity = ref(false);
const newActivity = ref('');
function updateActivities() {

}
async function submitActivity() {
  const res = await axios.post('/activities', {
    data: {
      activity: newActivity.value,
      plan_id: plan.id
    },
    activities: activities.value
  })
  addActivity.value = false
  console.log(res);
  // console.log(newActivity.value);
}
async function deleteActivity(id) {
  const res = await axios.delete(`/activities/${id}`, {
    data: {
      activities: activities.value.filter(act => act.id != id),
      plan_id: plan.id
    }
  })
  console.log(res);
  // console.log(newActivity.value);
}
</script>
<template>
  <div>Online Users</div>
  <ul>
    <li v-for="user in onlineUsers">{{ user.name }}</li>
  </ul>

  <div class="mt-20">
    <div>Activities :</div>
    <button @click="addActivity = true">Add Activity</button>
    <form action="" @submit.prevent="submitActivity" v-if="addActivity">
      <input type="text" v-model="newActivity">
    </form>
    <ul>
      <li v-for="activity in activities">
        <span>{{ activity.activity }}</span>
        <button class="ml-3" @click="deleteActivity(activity.id)">X</button>
      </li>
    </ul>
  </div>
</template>