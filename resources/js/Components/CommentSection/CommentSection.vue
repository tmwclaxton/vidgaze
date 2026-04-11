<script setup>
import {onMounted, ref, watch} from "vue";
import CommentTextarea from "@/Components/CommentSection/Partials/CommentTextarea.vue";
import Comment from "@/Components/CommentSection/Comment.vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";
import {useAuthStore} from "@/Stores/AuthStore";
import { openLoginModal } from '@/utils/authGate';
const CommentSectionStore = useCommentSectionStore();

const name = 'CommentSection';
const categoryOptions = [
    {value: 'best', label: 'Best'},
    {value: 'new', label: 'New'},
    {value: 'controversial', label: 'Controversial'},
    {value: 'old', label: 'Old'},
];


const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    simple: {
        type: Boolean,
        required: false,
        default: false
    },
    defaultCategory: {
        type: String,
        required: false,
        default: 'best'
    }
});

const category = ref(props.defaultCategory);

// watch for changes in category and fetch comments again
watch(category, (value) => {
    CommentSectionStore.getCommentInteractions();
    CommentSectionStore.fetchComments(category.value);
});


// grab comment slug from url if it exists and send that along with get request so it can be highlighted and put at top of comments

onMounted(() => {
    if (props.simple) {
        return;
    }
    CommentSectionStore.item = props.item;
    CommentSectionStore.item_type = props.item.type;


    // grab interactions first then comments
    setTimeout(() => {
        CommentSectionStore.getCommentInteractions().then(() => {
            // grab comment parameter from url if it exists
            const urlParams = new URLSearchParams(window.location.search);
            const comment = urlParams.get('comment');
            // once interactions are grabbed, fetch comments
            CommentSectionStore.fetchComments(category.value, null, comment);
        });


    }, 200); // 200ms delay

});


</script>

<template>


    <div id="comment_section" class=" flex flex-col w-full px-3">

        <!--number of comments and order by input (should only be visible when over 5 comments)-->
        <div class="grid  grid-cols-2">

            <p class="   text-base font-bold" v-text="CommentSectionStore.commentCount"/>

            <SelectInput class=" ml-auto w-40"
                          :modelValue="category"
                         v-model="category" @update:model-value="value => category = value" :options="categoryOptions" :title="'Order By'"/>


        </div>

        <div class="mb-3 w-full col-span-2 p-1">


            <div   v-if="useAuthStore().user !== null">
                <div  >
                    <CommentTextarea :item="item" :comment_id="null"/>

                    <!--<p class="text-red-500 font-semibold text-center">{{$error}}</p>-->

                </div>
                <div class=" w-full text dark:textDark  text-xs font-semibold opacity-50 text-center mt-5">
<!--                    This site is protected by reCAPTCHA. Google-->
<!--                    <a href="https://policies.google.com/privacy">Privacy Policy</a> and-->
<!--                    <a href="https://policies.google.com/terms">Terms of Service</a> apply.-->

                <!--- to add images img:https://static.vecteezy.com/system/resources/thumbnails/036/324/708/small/ai-generated-picture-of-a-tiger-walking-in-the-forest-photo.jpg --->
                       <p>To add an image use this format img:(url) i.e. "img:(www.google.com/logo)"</p>
                </div>
            </div>

            <button
                v-else
                type="button"
                class="text dark:textDark my-3 w-full border-b border-zinc-300 bg-transparent text-left text-sm leading-tight hover:border-zinc-400"
                @click="openLoginModal()"
            >
                <span class="font-semibold">Log in</span> to comment
            </button>

            <div class="flex flex-col w-full mt-5 mb-2" v-if="CommentSectionStore.comments.length > 0">

                <Comment v-if="CommentSectionStore.comments.length > 0"
                         v-for="comment in CommentSectionStore.comments.filter(comment => comment.parent_comment_id === null)"
                         :comment="comment" :key="comment.id" :item="props.item" />

            </div>

            <QuaternaryButton v-if="CommentSectionStore.comments.length > 9"  @click="CommentSectionStore.fetchComments(category, null, null, true);">
                <font-awesome-icon :icon="['fas', 'comments']" class="w-4 h-4"/>
                <span class="font-semibold">Load more</span>
            </QuaternaryButton>


        </div>



    </div>
</template>



