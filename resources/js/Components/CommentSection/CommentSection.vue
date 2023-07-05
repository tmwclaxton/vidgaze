<template>
    <div id="comment_section" class="  grid grid-cols-1 w-full">

        <!--number of comments and order by input (should only be visible when over 5 comments)-->
        <div class="grid  grid-cols-2">
            <p class="   text-base font-bold" v-text="item.comment_count"/>
            <!--<x-input name="select_plain" title="Order By" noHeading="true" class="ml-auto w-min"-->
            <!--         :options="['Best','New','Controversial','Old']"/>-->
        </div>

        <div class="mb-3 w-full col-span-2 p-1">


            <div  >
                <div  >
                <textarea @click="commentOptions = true" class="h-9 mb-3 mt-3 w-full peer block p-2 resize-none text-sm text dark:textDark  bg-transparent
                      border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-vidgaze-blue dark:border-zinc-600 dark:focus:border-zinc-400
                      focus:border-1 focus:ring-0 overflow-y-hidden"
                          placeholder="Leave a comment..."></textarea>

                    <div v-show="commentOptions" class=" justify-end w-full flex  ">
                        <button type="button" @click="commentOptions = false;"
                                class="mr-2 bg-white-200 text dark:textDark  focus:ring-zinc-200 max-w-min rect_button">
                            Cancel
                        </button>
                        <button type="button" @click="commentOptions = false;"
                                class="rect_button  text dark:textDark generic_button_2 dark:generic-background-dark_3 max-w-min mr-0">
                            Comment
                        </button>
                    </div>

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
