<script setup>
import {computed, ref} from "vue";
import TertiaryButton from "@/Components/Buttons/TertiaryButton.vue";
import LikeDislikeButtons from "@/Components/Buttons/LikeDislikeButtons.vue";
import {usePage} from "@inertiajs/vue3";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";

const CommentSectionStore = useCommentSectionStore();

const name = 'Comment';
const props = defineProps({
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
const editComment = ref(false);
const body = ref(props.comment.body);
const isCollapsed = ref(true);

const editable = computed(() => {
    // checked logged in and is owner of comment OR is admin
    const user = usePage().props.auth.user;

    return (user && (user.id === props.comment.owner.id || $page.props.auth.admin));

});

const share = () => {
    CommentSectionStore.shareComment(props.comment);


}
</script>

<template>
    <div class="w-full inline-flex flex-row mb-3 mt-1">


        <div class="flex flex-col w-full">
                <div id="comment" class='w-full  flex relative bg-zinc-100 dark:bg-zinc-900 p-4 px-3 rounded'>


                    <div class=" flex flex-row w-full">


                        <div class="w-9 mr-3 flex-shrink-0 ">
                            <a href="/channel/{{$comment.owner.slug}}">
                                <!--v-bind:href="route('channel', comment.owner.slug)"-->
                                <img
                                    class=" z-1 relative hover:cursor-pointer inline object-cover w-9 h-9  rounded-full"
                                    v-bind:src="comment.owner.avatar_url"
                                    alt="Profile image"/>
                            </a>
                        </div>


                        <div class="   flex-grow overflow-hidden">


                            <div class=" flex flex-row items-center">
                                <p>
                                    <a href="/channel/{{$comment.owner.slug}}">
                                    <span class="text-sm   font-semibold hover:cursor-pointer  leading-tight  " v-text="comment.owner.name" />
                                    </a>
                                    <span class="mx-2 text dark:textDark font-bold leading-tight"> · </span>
                                    <span class="text-sm font-semibold     leading-tight" v-text="comment.created_at"/>

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


                            <p v-show="!editComment" class=" pr-2 pt-1 break-words   "
                               v-bind:class="{' line-clamp-3': !isCollapsed}" v-html="body"/>

                            <div v-show="editComment && editable">
                                <form method="POST">
                                    <!--<textarea id="message" style="min-height: 30px;"-->
                                    <!--          class="generic-textarea dark:generic-textarea-dark"-->
                                    <!--          placeholder="Edit your comment...">{{$comment->body}}</textarea>-->
                                    <!--<div class="flex justify-end flex-row pt-2">-->
                                    <!--    <button type="button" @click="editComment = false;"-->
                                    <!--            class="rect_button  mr-2 max-w-min without-ring">-->
                                    <!--        Cancel-->
                                    <!--    </button>-->
                                    <!--    <button type="submit" @click="editComment = false;"-->
                                    <!--            class="rect_button  text dark:textDark generic_button_2 dark:generic-background-dark_3 max-w-min mr-0">-->
                                    <!--        Save-->
                                    <!--    </button>-->
                                    <!--</div>-->
                                </form>
                            </div>

                            <button v-if="comment.body.length > 250 || (simple && comment.body.length > 73)"
                                    class="font-bold my-2 text-xs uppercase"
                                    @click="isCollapsed = !isCollapsed"
                                    v-text="isCollapsed ? 'Show less' : 'Show more'"
                            ></button>

                            <div class="  flex flex-row gap-x-2  font-semibold pt-3 hover:cursor-pointer select-none">


                                <TertiaryButton>
                                    <LikeDislikeButtons  :orientation-vertical="false" :comment="comment" :setLikeValue="CommentSectionStore.getCommentInteraction(comment.id)" />
                                </TertiaryButton>

                                <span v-if="!simple" @click="comment = ! comment" >
                                    <!--<x-comment-button class="w-4" svgIcon="message" text="Reply"/>-->
                                    <TertiaryButton>
                                        <font-awesome-icon :icon="['fas', 'comment']" class="h-5 aspect-square"/>
                                        <span class="text-sm font-semibold">Reply</span>
                                    </TertiaryButton>
                                </span>

                                <span @click="editComment = !editComment">
                                    <TertiaryButton>
                                        <font-awesome-icon :icon="['fas', 'pencil']" class="h-5 aspect-square"/>
                                        <span class="text-sm font-semibold">Edit</span>
                                    </TertiaryButton>
                                </span>

                                <span >
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
                                    <tertiary-button >
                                        <font-awesome-icon :icon="['fas', 'reply']" class="h-5 aspect-square"/>
                                        <span class="text-sm font-semibold">Share</span>
                                    </tertiary-button>
                                </span>



                                <!--<p class="ml-auto mr-2  uppercase text-xs font-bold inline">Pinned</p>-->


                            </div>
                            <div class="w-full flex flex-col" v-if="!simple && comment.reply_count > 0">
                                <span  class="select-none w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <!--<x-icon name="extend-down" class="fill-blue-600 h-3 my-auto mr-2"/>-->
                                        <!--<p class="font-bold">View {{$comment->replies->count()}} replies</p>-->
                                    </span>
                                <span class="select-none w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <!--<x-icon style="transform: scale(-1, -1);" name="extend-down" class="fill-blue-600 h-3 my-auto mr-2"/>-->
                                        <!--<p class="font-bold">Minimise</p>-->
                                    </span>
                            </div>
                        </div>

                    </div>


                </div>

            </div>

    </div>
</template>

