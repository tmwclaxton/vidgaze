<template>
    <div id="comment_section" class=" flex flex-col w-full">

        <!--number of comments and order by input (should only be visible when over 5 comments)-->
        <div class="grid  grid-cols-2">
            <p class="   text-base font-bold" v-text="item.comment_count"/>
            <!--<x-input name="select_plain" title="Order By" noHeading="true" class="ml-auto w-min"-->
            <!--         :options="['Best','New','Controversial','Old']"/>-->
        </div>

        <div class="mb-3 w-full col-span-2 p-1">


            <div  >
                <div  >
                    <CommentTextarea :item="item" :comment_id="null" />

                    <!--<p class="text-red-500 font-semibold text-center">{{$error}}</p>-->

                </div>
                <div class="w-full text dark:textDark  text-xs font-semibold opacity-50 text-center mt-5">
                    This site is protected by reCAPTCHA. Google
                    <a href="https://policies.google.com/privacy">Privacy Policy</a> and
                    <a href="https://policies.google.com/terms">Terms of Service</a> apply.
                </div>
            </div>

            <!--<a v-else v-bind:href="route('login')"-->
            <!--   class="text dark:textDark  text-sm leading-tight my-3 w-full border-b-1 border-zinc-300"><span-->
            <!--    class="font-semibold"> Log in </span> to comment-->
            <!--</a>-->

        </div>



    </div>
</template>


<script setup>
import {ref} from "vue";
import CommentTextarea from "@/Components/CommentSection/Partials/CommentTextarea.vue";

const name = 'CommentSection';
const commentOptions = ref(false);
const comment_id = ref(null);
const comment_text = ref(null);

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
});
</script>

<style scoped>

</style>
