
<script setup>

import ClockIcon from '#icons/clock.svg';
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
const name = "VideoStreamModal";

</script>



<template>
    <div class="z-[100] fixed bottom-0 right-0 m-16 ">



        <OptionHolder>
                <span>
                    <Option  >
                        <ClockIcon class="w-5 h-5 mr-2" />
                        <p>Save to Watch later</p>
                    </Option>
                </span>
        </OptionHolder>

    </div>
</template>

