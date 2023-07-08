<script setup>
import {onMounted, ref} from "vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import {useToastStore} from "@/Stores/ToastStore";

import {usePage} from "@inertiajs/vue3";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";
const confirmStore = useConfirmModalStore();
const toastStore = useToastStore();
const commentSectionStore = useCommentSectionStore();
const name = 'CommentTextarea';
const commentOptions = ref(false);
const comment = ref('');
const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    comment_id: {
        type: Number,
        required: false,
        default: null
    }
});

const submitComment = () => {
    try {
        this.$refs.honeypot.validate();
    } catch (error) {
        // error handling
    }

    commentSectionStore.storeComment(comment.value, props.item.id, props.item.type, props.comment_id)
    commentOptions.value = false
    comment.value = ''
    resetTextArea()

}

const resizeTextarea = (event) => {
    event.target.style.height = '5px';
    event.target.style.height = event.target.scrollHeight + 'px';
}

const textarea = ref(null);
function resetTextArea() {
    comment.value = '';
    commentOptions.value = false;
    // remove style attribute
    textarea.value.removeAttribute('style');
}

const cancelComment = () => {
    if (comment.value.length > 0) {
        // confirm that user wants to cancel comment
        confirmStore.buttonOneText = 'Go Back';
        confirmStore.buttonTwoText = 'Delete';
        confirmStore.title = 'Are you sure, this will delete your comment?';
        confirmStore.show = true;
        confirmStore.continue = () => {
            commentOptions.value = false;
            resetTextArea();
        };
    } else {
        commentOptions.value = false;
    }
};


</script>


<template>
    <form @submit.prevent="submit" class="w-full">
        <textarea  ref="textarea" v-model="comment" @input="resizeTextarea($event)"
            @click="commentOptions = true" class="h-9 mb-3 mt-3 w-full peer block p-2 resize-none text-sm text dark:textDark  bg-transparent
          border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-zinc-400 dark:border-zinc-600 dark:focus:border-zinc-400
          focus:border-1 focus:ring-0 overflow-y-hidden"
                 placeholder="Leave a comment..."></textarea>

        <!--<VueHoneyPot ref="honeypot"/>-->

        <div v-show="commentOptions" class=" justify-end w-full flex  ">

            <QuaternaryButton class="mr-2" @click=" cancelComment">
                <font-awesome-icon :icon="['fas', 'ban']" class="w-4 h-4"/>
                <span class="font-semibold">Cancel</span>
            </QuaternaryButton>
            <QuaternaryButton class="mr-2" @click="submitComment">
                <font-awesome-icon :icon="['fas', 'comment']" class="w-4 h-4"/>
                <span class="font-semibold">Comment</span>
            </QuaternaryButton>

        </div>
    </form>
</template>
