<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import OutlineButton from '@/Components/OutlineButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import { ClassicEditor, Bold, Essentials, Italic, Mention, Paragraph, Undo, BalloonEditor } from 'ckeditor5';
import MyEditor from '@/Components/MyEditor.vue';
import Popover from 'primevue/popover';
import { computed, onMounted, ref } from 'vue';
import moment from 'moment';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { dataURLtoFile, formAxios, previewImage } from '@/util';
import axios from 'axios';
import PreviewLayout from '@/Layouts/PreviewLayout.vue';
import Story from './Story.vue';
import { GoogleGenAI } from '@google/genai';
// import { GoogleGenerativeAI } from '@google/generative-ai';
import Drawer from 'primevue/drawer';
import Textarea from 'primevue/textarea';
import IconButton from '@/Components/IconButton.vue';
import InputText from 'primevue/inputtext';
import { marked } from 'marked';
import Loading from '@/Components/Loading.vue';

// defineOptions({ layout: DashboardLayout });
const { plan } = defineProps(['plan'])
const sectionlist = ref();
function toggleSection(event) {
  sectionlist.value.toggle(event)
}
const mainForm = useForm({
  title: plan.name,
  body: '',
  picture: null,
  pictureInBody: []
})
const imageInput = ref()
const showCover = ref(true)
const previewCover = computed(() => URL.createObjectURL(mainForm.picture))
const sections = ref(plan.activities.map(act => ({
  ...act,
  section: false,
  title: '',
  body: '',
  form: useForm({
    title: '',
    body: '',
  })
})))
function addSection(activity) {
  activity.section = true
  activity.form.title = activity.place.name
  sectionlist.value.hide()
}
function cancelSection(activity) {
  activity.section = false
  // activity.title = activity.place.name
  // activity.body = ''
  activity.form.reset()
  activity.form.clearErrors()
  sectionlist.value.hide()
}

const fullBody = computed(() => mainForm.body + sections.value.filter(sect => sect.section).map(sect => `<h2 id="${sect.place_id}">${sect.form.title}</h2>${sect.form.body}`).join(''))
async function publish() {
  const fullBody = mainForm.body + sections.value.filter(sect => sect.section).map(sect => `<h2 id="${sect.place_id}">${sect.form.title}</h2>${sect.form.body}`).join('')

  const bodyHTML = document.createElement('div')
  bodyHTML.innerHTML = fullBody
  const pictureInBody = [...bodyHTML.querySelectorAll('figure img')].map(img => dataURLtoFile(img.src, 'img.png'));
  // const data = new FormData()
  // pictureInBody.forEach(pic => data.append('pictureInBody[]', pictureInBody))
  // data.append('title', mainForm.title);
  const { data: fileNames } = await formAxios.post('/upload-image-body', { pictureInBody, title: mainForm.title }, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
  bodyHTML.querySelectorAll('figure img').forEach((img, i) => img.src = fileNames[i])
  // console.log(bodyHTML.innerHTML);
  // console.log(fileNames);
  // console.log(fullBody);
  sections.value.forEach(section => {
    if (section.section) {
      section.form.clearErrors()
      if (section.form.title == '')
        return section.form.setError('title', 'Title field is required')
      if (section.form.body == '')
        return section.form.setError('body', 'Body field is required')
    }
  })
  mainForm.transform(data => ({ ...data, body: bodyHTML.innerHTML })).post(`/dashboard/plans/${plan.public_id}/story`)
}
const preview = ref(false)

const ai = ref(false)
const prompt = ref('')
const aiResult = ref(null)
const loadingAI = ref(false)
const genAI = new GoogleGenAI({ apiKey: import.meta.env.VITE_GEMINI_API_KEY })
// const genAI = new GoogleGenerativeAI(import.meta.env.VITE_GEMINI_API_KEY)
// const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });
const chat = genAI.chats.create({
  model: "gemini-2.5-flash"
})
// const chat = model.startChat()
const chatHistory = ref();
async function getAI() {
  // const result = await model.generateContent(prompt.value);
  loadingAI.value = true
  document.querySelector('.p-drawer-content').scrollTo(0, document.querySelector('.p-drawer-content').scrollHeight);
  const result = await chat.sendMessageStream({ message: prompt.value });
  for await (const chunk of result) {
    console.log(chunk.text);
    aiResult.value = chunk.text;
  }
  // const result = await chat.sendMessage({ message: prompt.value });
  // const response = await result.response;
  prompt.value = '';
  chatHistory.value = await chat.getHistory();
  loadingAI.value = false
  // console.log(text);
}
</script>
<template>
  <DashboardLayout v-if="!preview">
    <div
      class="grid grid-cols-[auto_max-content_max-content] md:grid-cols-[max-content_max-content_auto_max-content_max-content] grid-rows-[repeat(2, max-content)] gap-x-4 gap-y-8">
      <Button severity="contrast" outlined @click="$refs.imageInput.click()"
        class="order-last md:order-none w-fit col-span-3 !border-0 md:col-span-1 md:!border">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        {{ mainForm.picture ? 'Change' : 'Add' }} cover
      </Button>
      <input type="file" class="hidden" ref="imageInput" @input="mainForm.picture = $event.target.files[0]"
        accept="image/*">
      <OutlineButton @click="preview = true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        Preview
      </OutlineButton>
      <div class="order-first md:order-none"></div>
      <PrimaryButton @click="publish">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
        </svg>
        Publish
      </PrimaryButton>
      <OutlineButton @click="ai = true"
        class="!size-12 md:!size-auto !rounded-full md:!rounded-lg flex justify-center !p-0 md:!px-3 md:!py-2 fixed bottom-4 right-4 md:static">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
        <span class="hidden md:inline">AI Assistant</span>
      </OutlineButton>
    </div>
    <div v-if="mainForm.picture">
      <button @click="showCover = !showCover" type="button"
        class="px-2 py-1.5 rounded-md hover:bg-gray-100 my-2 text-sm">
        <svg v-if="showCover" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
          stroke="currentColor" class="size-6 inline mr-2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <svg v-if="!showCover" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
          stroke="currentColor" class="size-6 inline mr-2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
        </svg>
        <span>{{ showCover ? 'Hide' : 'Show' }} cover</span>
      </button>
      <div class="relative" v-if="showCover">
        <img :src="previewCover" alt=""
          class="image-cover w-full h-[15rem] md:h-[25rem] object-cover object-center rounded-md">
        <button
          class="absolute top-5 left-5 w-10 h-10 rounded-md bg-gray-300 hover:bg-gray-400 bg-opacity-50 flex justify-center items-center text-2xl"
          title="Remove cover" @click="mainForm.picture = null">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    <div>
      <input type="text" id="title" v-model="mainForm.title"
        class="text-4xl font-semibold block mt-8 mb-3 w-full plain-input" placeholder="Blog title...">
      <MyEditor v-model="mainForm.body" />
      <hr>
    </div>
    <div v-for="section in sections.filter(sect => sect.section)" class="relative section">
      <input v-model="section.form.title" type="text" id="title"
        class="text-2xl font-semibold block mt-4 mb-3 w-full plain-input rounded-md"
        :class="section.form.errors.title ? 'error' : ''" placeholder="Section title...">
      <div class="text-red-600">{{ section.form.errors.title }}</div>
      <MyEditor v-model="section.form.body" :error="!!section.form.errors.body" />
      <div class="text-red-600">{{ section.form.errors.body }}</div>
      <hr class="block mt-2">
      <button class="w-8 h-8 rounded-md flex justify-center items-center hover:bg-gray-100 absolute top-2 -left-8"
        @click="cancelSection(section)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <a href="#" v-if="sections.filter(sect => !sect.section).length > 0" class="flex gap-1 text-blue-primary mt-4"
      @click="toggleSection">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      <span>Add section based on activity</span>
    </a>
    <Popover v-if="sections.filter(sect => !sect.section).length > 0" ref="sectionlist">
      <div class="w-64">
        <a href="#" class="px-4 py-2.5 border-b last:border-b-0 block hover:bg-gray-50 active:bg-gray-100"
          v-for="activity in sections.filter(sect => !sect.section)" @click="addSection(activity)">
          <div>{{ activity.place.name }}</div>
          <small class="text-gray-600">{{ moment.utc(activity.time).local().format('MMMM D, HH:mm') }}</small>
        </a>
      </div>
    </Popover>

    <Drawer v-model:visible="ai" header="AI Assistant" position="right" class="!w-[30rem] max-w-full">
      <div class="flex flex-col gap-4 items-end">
        <template v-for="chat in chatHistory">
          <div class="prose"
            :class="chat.role == 'user' ? 'w-[90%] px-3 py-2 rounded-md bg-gray-100 mt-2 first:mt-0' : 'w-full'"
            v-html="marked.parse(chat.parts.map(c => c.text).join(''))"></div>
        </template>
      </div>
      <div v-if="!chatHistory && !loadingAI" class="flex flex-col justify-center items-center gap-8 h-full">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="size-12">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
        </svg>
        <div class="w-full grid grid-cols-2 gap-4">
          <button class="block w-full h-full p-3 rounded-lg border text-left hover:bg-gray-50"
            @click="prompt = 'Describe about Sanur'; getAI()">Describe about
            Sanur</button>
          <button class="block w-full h-full p-3 rounded-lg border text-left hover:bg-gray-50"
            @click="prompt = `Write article with title 'Trip to Bali'`; getAI()">
            Write article with title "Trip to Bali"
          </button>
        </div>
      </div>
      <Loading v-if="loadingAI" />
      <form action="" @submit.prevent="getAI" class="flex gap-1 items-center fixed bottom-3 left-0 right-0 px-4">
        <Textarea name="" id="" v-model="prompt" placeholder="Type Message" :readonly="loadingAI"
          class="block flex-grow !max-h-[12rem]" rows="1" auto-resize />
        <button class="w-9 h-9 flex justify-center items-center rounded-md hover:bg-gray-100">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
          </svg>
        </button>
      </form>
    </Drawer>
  </DashboardLayout>

  <PreviewLayout v-if="preview" @back="preview = false" @publish="publish">
    <Story :preview="true" :story="{
      title: mainForm.title,
      picture: mainForm.picture ? previewImage(mainForm.picture) : null,
      body: fullBody,
      created_at: moment.utc(),
      user: $page.props.auth.user,
    }"></Story>
  </PreviewLayout>
</template>
<style>
.plain-input,
.plain-input:focus {
  outline: none !important;
  border: none;
  box-shadow: none !important;
  /* Pastikan box-shadow juga dihapus */
}

.plain-input.error,
.plain-input.error:focus {
  border: 1px solid #dc2626;
}
</style>