<template>
    <div  class="relative flex">
              <!--:class="{ 'opacity-100': awardDropdown }"opacity-0 -->
        <div
             class="fixed block text-black  m-auto option bg-white dark:bg-zinc-800 dark:text-white
                  left-0 right-0 top-0 bottom-0 w-4/5 lg:w-1/2  h-96 z-20
                  shadow-lg option rounded overflow-y-hidden">

            <div class="w-full flex flex-row p-4">
                <font-awesome-icon  :icon="['fas', 'xmark']"
                                    class="w-5 h-5 k my-auto cursor-pointer" @click="closeDropdown"/>
                <p class="k text-lg my-auto font-bold ml-3">Awards</p>
                <a :href="marketplaceUrl" class="ml-auto flex flex-row ">
                    <primary-button class="flex flex-row px-4  align-middle" >
                        <img src="/images/vidcoins/coins/PileofCoins2.png" class="w-4 h-4 my-auto k"/>
                        <p class="font-bold ml-3 text-sm my-auto " v-text="useAuthStore().user !== null ? useAuthStore().user.creator.vidcoins : 'Log in'"></p>
                        <div style="width: 1px;" class="h-full mx-3 bg-transparent border border-zinc-200 dark:border-zinc-600"/>
                        <font-awesome-icon :icon="['fas', 'plus']" class="w-4 h-4 my-auto k"/>
                    </primary-button>
                </a>
            </div>
            <div class="border border-b-0 border-zinc-200 dark:border-zinc-600"/>
            <div class="h-80 flex flex-row relative">
                <div class="overflow-y-scroll flex-grow grid grid-cols-2 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 p-3">
                    <div v-for="award in awards" :key="award.id" tabindex="0" class="flex flex-col items-center p-2 border border-zinc-200 dark:border-zinc-600 rounded-lg cursor-pointer"
                         :class="{'border-blue-500 dark:border-blue-500': selectedAward.id === award.id}"
                         @click="selectAward(award)">
                        <img class="mx-auto h-10 mx-auto" :src="award.icon_url"/>
                        <p class="k text-xs font-bold text-center mt-2 ">
                            {{ numberFormatShort(award.coin_price) }}
                        </p>
                    </div>
                </div>
                <div class="h-full relative border-y-0 border-r-0 border-zinc-200 dark:border-zinc-600">
                    <div class="my-auto m-0 p-0 flex-shrink-0 w-48 md:w-64">
                        <img class="mt-10 mx-auto h-24 mb-2 mx-auto" :src="selectedAward.icon_url"/>
                        <p class="k font-bold text-center my-2">{{ selectedAward.name }}</p>
                        <p class="k font-bold text-center my-2">{{ selectedAward.coin_price }} VidCoins</p>
                        <p class="k text-sm text-center px-6 line-clamp-3">{{ selectedAward.description }}</p>
                        <div class="mx-2 mt-6 flex flex-row justify-center">
                            <primary-button  v-if="useAuthStore().user !== null && checkBal()" @click="submitAward" class=" p-2 rounded w-max font-bold k">
                                Give Award
                            </primary-button>
                            <Link v-else
                                   :href="route('login')" >
                                <primary-button  class=" p-2 rounded w-max font-bold k">
                                    Login
                                </primary-button>
                            </Link>
                        </div>
                        <!--<input type="hidden" v-model="type">-->
                        <!--<input type="hidden" v-model="award_id">-->
                        <!--<input type="hidden" v-model="object_id">-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import {ref, onMounted} from 'vue';
import axios from 'axios';
import {useAuthStore} from "@/Stores/AuthStore";
import {useToastStore} from "@/Stores/ToastStore";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";

const awards = ref([]); // To hold award data
const awardDropdown = ref(false);
const selectedAward = ref({
    name: '',
    icon_url: '',
    coin_price: 0,
    description: ''
});

const props = defineProps({
    type: String,
    object_id: String
});

const award_id = ref(1);
const marketplaceUrl = ref('/marketplace');

const authStore = useAuthStore();
const toastStore = useToastStore();

const fetchAwards = async () => {
    try {
        const response = await axios.get(route('api.award.index'));
        awards.value = response.data;
        if (awards.value.length > 0) {
            selectedAward.value = awards.value[0];
        }
    } catch (error) {
        console.error('Error fetching awards:', error);
    }
};

const selectAward = (award) => {
    console.log("Selected award: ", award.id);
    selectedAward.value = award;
    award_id.value = award.id;
};

const numberFormatShort = (value) => {
    return new Intl.NumberFormat().format(value);
};

const checkBal = () => {
    return authStore.user.creator.vidcoins_int >= selectedAward.value.coin_price;
};

const submitAward = async () => {
    if (!checkBal()) {
        toastStore.add({
            message: "You do not have enough VidCoins to give this award.",
            type: "warning"
        });
        return;
    }
    try {
        await axios.post(route('api.award.award'), {
            award_id: award_id.value,
            type: props.type,
            object_id: props.object_id
        });
        await fetchAwards(); // Refresh awards after giving one
        authStore.toggleAwardDropdown();
        toastStore.add({
            message: "Awarded successfully.",
            type: "success"
        });
    } catch (error) {
        toastStore.add({
            message: "Error awarding.",
            type: "warning"
        });
    }
};

const closeDropdown = () => {
    authStore.showAwardDropdown = false;
};

onMounted(fetchAwards);
</script>

<style scoped>
/* Add component-specific styles here */
</style>

