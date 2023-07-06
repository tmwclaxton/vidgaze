<script setup>
import {onMounted, ref} from "vue";
import CommentTextarea from "@/Components/CommentSection/Partials/CommentTextarea.vue";
import Comment from "@/Components/CommentSection/Comment.vue";

const name = 'CommentSection';
const comments = ref([]);

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
});


// grab comment slug from url if it exists and send that along with get request so it can be highlighted and put at top of comments

onMounted(() => {
    // axios.get('/api/comments/' + props.item.id)
    //     .then(response => {
    //         comments.value = response.data;
    //     })
    //     .catch(error => {
    //         console.log(error);
    //     });
});

</script>

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

            <div v-if="item.comment_count > 0" class="flex flex-col w-full  ">

                <Comment/>

                <!--<x-button wire:click="loadMore" name="rect_button"-->
                <!--          class=" mt-1 w-full generic_button_2">-->
                <!--    Load More Comments-->
                <!--</x-button>-->
                <!--<x-error-message image_url="/images/mascot/ThumbsUp.png" :explore="false" text="Looks like there aren't any comments yet.  Be the first!"/>-->

            </div>



        </div>



    </div>
</template>



