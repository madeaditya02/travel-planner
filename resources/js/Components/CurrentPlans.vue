<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import CurrentPlanCard from './CurrentPlanCard.vue';
import 'swiper/css';
import 'swiper/css/pagination';
defineEmits(['sharePlan', 'simple'])
defineProps(['currentPlans'])
</script>
<template>
  <div class="current-plan">
    <swiper :slidesPerView="'auto'" :spaceBetween="30" :pagination="{
      clickable: true,
    }">
      <swiper-slide v-for="plan in currentPlans">
        <current-plan-card @share-plan="$emit('sharePlan', plan)" :plan="plan" />
      </swiper-slide>
      <swiper-slide></swiper-slide>
    </swiper>
  </div>
  <h5 v-if="!currentPlans?.length" class="text-xl text-gray-600">No current plan yet</h5>
</template>
<style>
.swiper {
  width: 100%;
  height: auto;
}

.current-plan .swiper-slide {
  width: 85%;
}

.current-plan .swiper-slide:last-child {
  width: calc(15% - 30px);
}
</style>