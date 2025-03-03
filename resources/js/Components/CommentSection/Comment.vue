<script setup>
import {computed, ref} from "vue";
import TertiaryButton from "@/Components/Buttons/TertiaryButton.vue";
import LikeDislikeButtons from "@/Components/Buttons/LikeDislikeButtons.vue";
import {usePage} from "@inertiajs/vue3";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import CommentTextarea from "@/Components/CommentSection/Partials/CommentTextarea.vue";
import {useAuthStore} from "@/Stores/AuthStore";
const confirmStore = useConfirmModalStore();
const CommentSectionStore = useCommentSectionStore();

const name = 'Comment';
const editComment = ref(false);
const isCollapsed = ref(true);
const replyComment = ref(false);

const editable = computed(() => {
    // checked logged in and is owner of comment OR is admin
    const user = useAuthStore().user;

    return (user && (user.creator.id === props.comment.owner.id || useAuthStore().admin));

});
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    comment: {
        type: Object,
        required: true
    },
    simple: {
        type: Boolean,
        required: false,
        default: false
    }
});

const share = () => {
    CommentSectionStore.shareComment(props.comment);
}

const deleteComment = () => {
    // confirm that user wants to delete comment
    confirmStore.buttonOneText = 'Cancel';
    confirmStore.buttonTwoText = 'Delete';
    confirmStore.title = 'Are you sure, this will delete this comment?';
    confirmStore.show = true;
    confirmStore.continue = () => {
        CommentSectionStore.deleteComment(props.comment.id);
        confirmStore.show = false;
    };
};

// replies computed text pluralization
const noRepliesText = computed(() => {
    if (props.comment.reply_count === 1) {
        return props.comment.reply_count + ' Reply';
    } else {
        return props.comment.reply_count + ' Replies';
    }
});



</script>

<template>
    <div class="flex flex-row gap-x-2"  :class=" props.comment.parent_comment_id === null ? 'my-2' : ' '">

        <div v-if="props.comment.parent_comment_id != null" class=" w-0.5 col-span-1  g-zinc-100 dark:bg-zinc-900 mt-2"></div>


        <!--if parent comment id is set then no margin bottom otherwise mb-3-->
        <div class="w-full inline-flex flex-col " >

                <div class="flex flex-col w-full">
                    <div id="comment" class='w-full  flex flex-row relative  ' :class=" props.comment.parent_comment_id != null ? 'mt-2' : ' '">


                        <div class=" flex flex-row w-full bg-zinc-100 dark:bg-zinc-900 p-4 px-3 rounded-xl">


                            <div class="w-9 mr-3 flex-shrink-0 ">
                                <Link :href="route('channel.show', props.comment.owner.slug)">
                                    <img
                                        class=" z-1 relative hover:cursor-pointer inline object-cover w-9 h-9  rounded-full"
                                        v-bind:src="props.comment.owner.avatar_url"
                                        alt="Profile image"/>
                                </Link>
                            </div>


                            <div class="   flex-grow overflow-hidden">


                                <div class=" flex flex-row items-center">
                                    <p class="">
                                        <Link :href="route('channel.show', props.comment.owner.slug)">

                                            <span class="text-sm   font-semibold hover:cursor-pointer  leading-tight  "
                                                  v-text="props.comment.owner.name"/>
                                        </Link>
                                        <span class="mx-2 text dark:textDark font-bold leading-tight"> · </span>
                                        <span class="text-sm font-semibold     leading-tight"
                                              v-text="props.comment.created_at"/>


                                    </p>

                                    <!--awards &-->
                                    <!--<p class="mx-2 text dark:textDark"></p>-->
                                    <!--@if(!$simple)-->
                                    <!--<livewire:awards-bar type="comment" :object="$comment"/>-->
                                    <!--@else-->
                                    <!--I am a hack to keep placement consistent-->
                                    <!--<p class="mx-2 text dark:textDark"></p>-->
                                    <!--@endif-->
                                </div>


                                <!--comment body here -->
                                <p v-show="!editComment" class=" pr-2 pt-1 break-words   "
                                   v-bind:class="{' line-clamp-3': !isCollapsed}" v-html="props.comment.body"/>

                                <!--<div v-if="props.comment.edited && !editComment" class="">-->
                                <!--    <span class="text-xs italic text-red-600 dark:text-red-400 font-semibold ">-->
                                <!--        Edited-->
                                <!--    </span>-->
                                <!--</div>-->

                                <div v-show="editComment && editable">
                                    <CommentTextarea :body="props.comment.body" :comment_id="props.comment.id" action="edit"
                                                     @close="editComment = false" />
                                </div>

                                <button v-if="comment.body.length > 250 || (simple && comment.body.length > 73)"
                                        class="font-bold my-2 text-xs uppercase"
                                        @click="isCollapsed = !isCollapsed"
                                        v-text="isCollapsed ? 'Show less' : 'Show more'"
                                ></button>

                                <div class="  flex flex-row flex-wrap gap-2  font-semibold pt-3 hover:cursor-pointer ">


                                    <TertiaryButton >
                                        <LikeDislikeButtons :orientation-vertical="false" :comment="comment" :item="props.item" :key="'comment' + comment.id"
                                                            :setLikeValue="CommentSectionStore.getCommentInteraction(comment.id)"/>
                                    </TertiaryButton>

                                    <span v-if="!simple && useAuthStore().user !== null" @click="replyComment = !replyComment">
                                        <!--<x-comment-button class="w-4" svgIcon="message" text="Reply"/>-->
                                        <TertiaryButton>
                                            <font-awesome-icon :icon="['fas', 'comment']" class="h-5 aspect-square"/>
                                            <span class="text-sm font-semibold">Reply</span>
                                        </TertiaryButton>
                                    </span>

                                    <span v-if="editable" @click="editComment = !editComment">
                                        <TertiaryButton>
                                            <font-awesome-icon :icon="['fas', 'pencil']" class="h-5 aspect-square"/>
                                            <span class="text-sm font-semibold">Edit</span>
                                        </TertiaryButton>
                                    </span>

                                    <span v-if="editable" @click="deleteComment">
                                        <TertiaryButton>
                                            <font-awesome-icon :icon="['fas', 'trash']" class="h-5 aspect-square"/>
                                            <span class="text-sm font-semibold">Delete</span>
                                        </TertiaryButton>
                                    </span>

                                    <!--award button-->
                                    <!--<span>-->
                                    <!--    <tertiary-button >-->
                                    <!--        <font-awesome-icon :icon="['fas', 'gift']" class="h-5 aspect-square"/>-->
                                    <!--        <span class="text-sm font-semibold">Award</span>-->
                                    <!--    </tertiary-button>-->
                                    <!--</span>-->


                                    <!--share button-->
                                    <span @click="share()">
                                        <tertiary-button>
                                            <font-awesome-icon :icon="['fas', 'reply']" class="h-5 aspect-square"/>
                                            <span class="text-sm font-semibold">Share</span>
                                        </tertiary-button>
                                    </span>


                                    <!--<p class="ml-auto mr-2  uppercase text-xs font-bold inline">Pinned</p>-->


                                </div>
                                <div class="w-full flex flex-col cursor-pointer w-max"
                                     v-if="comment.reply_count > 0" @click="isCollapsed = !isCollapsed">
                                    <span v-if="isCollapsed"
                                          @click="CommentSectionStore.fetchComments('new', comment.id, null, true)"
                                          class=" w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <font-awesome-icon :icon="['fas', 'caret-down']"
                                                           class="fill-blue-600 h-3 my-auto mr-2"/>
                                        <p class="font-bold">View {{ noRepliesText }}</p>
                                    </span>
                                    <span v-else
                                          class=" w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <font-awesome-icon :icon="['fas', 'caret-up']"
                                                           class="fill-blue-600 h-3 my-auto mr-2"/>
                                            <p class="font-bold">Minimise</p>
                                        </span>
                                </div>
                            </div>

                        </div>


                    </div>

                    <CommentTextarea :body="props.comment.body" :comment_id="props.comment.id" action="reply"
                                     @close="replyComment = false; isCollapsed = false" v-if="replyComment"/>


                </div>
            <Comment v-if="!isCollapsed" v-for="comment in CommentSectionStore.comments.filter(comment =>  parseInt(comment.parent_comment_id) === props.comment.id )"
                     :comment="comment" :key="comment.id"  :item="props.item"/>
            </div>
    </div>
</template>

