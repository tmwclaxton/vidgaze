<script setup>
import {ref} from "vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";

const name = 'CommentTextarea';
const commentOptions = ref(false);
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
</script>


<template>
    <textarea @click="commentOptions = true" class="h-9 mb-3 mt-3 w-full peer block p-2 resize-none text-sm text dark:textDark  bg-transparent
          border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-zinc-400 dark:border-zinc-600 dark:focus:border-zinc-400
          focus:border-1 focus:ring-0 overflow-y-hidden"
              placeholder="Leave a comment..."></textarea>

    <div v-show="commentOptions" class=" justify-end w-full flex  ">

        <QuaternaryButton class="mr-2" @click="commentOptions = false;">
            <font-awesome-icon :icon="['fas', 'ban']" class="w-4 h-4"/>
            <span class="font-semibold">Cancel</span>
        </QuaternaryButton>
        <QuaternaryButton class="mr-2" @click="commentOptions = false;">
            <font-awesome-icon :icon="['fas', 'comment']" class="w-4 h-4"/>
            <span class="font-semibold">Comment</span>
        </QuaternaryButton>

    </div>
</template>
