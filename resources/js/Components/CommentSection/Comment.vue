<script setup>
import {computed, ref} from "vue";

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
const editable = computed(() => {
    // checked logged in and is owner of comment OR is admin
    const user = $page.props.auth.user;

    return (user && (user.id === props.comment.owner.id || $page.props.auth.admin));

});
</script>

<template>
    <div class="w-full inline-flex flex-row mb-3 mt-1">


        <div class="flex flex-col w-full">
                <div id="comment" class='w-full  flex group relative text dark:textDark generic-background_2 dark:generic-background-dark_2 p-4 px-3 rounded'>


                    <div class=" flex flex-row w-full">


                        <div class="w-9 mr-3 flex-shrink-0 ">
                            <a href="/channel/{{$comment->owner->slug}}">
                                <img
                                    class=" z-1 relative hover:cursor-pointer inline object-cover w-9 h-9  rounded-full"
                                    src="{{$comment->owner->avatar_url}}"
                                    alt="Profile image"/>
                            </a>
                        </div>


                        <div class="   flex-grow overflow-hidden">


                            <div class=" flex flex-row items-center">
                                <p>
                                    <a href="/channel/{{$comment->owner->slug}}">
                                    <span
                                        class="text-sm   font-semibold hover:cursor-pointer  leading-tight  ">  {{$comment->owner->name}} </span>
                                    </a>
                                    <span class="mx-2 text dark:textDark font-bold leading-tight"> · </span>
                                    <span
                                        class="text-sm font-semibold     leading-tight  ">{{$comment->created_at->diffForHumans()}}</span>

                                </p>

                                <!--{{&#45;&#45; awards &#45;&#45;}}-->
                                <!--<p class="mx-2 text dark:textDark"></p>-->
                                <!--@if(!$simple)-->
                                <!--<livewire:awards-bar type="comment" :object="$comment"/>-->
                                <!--@else-->
                                <!--{{&#45;&#45;I am a hack to keep placement consistant&#45;&#45;}}-->
                                <!--<p class="mx-2 text dark:textDark"></p>-->
                                <!--@endif-->
                            </div>


                            <p v-model="body" v-show="!editComment" class=" pr-2 pt-1 break-words   "
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

                            @if(strlen($comment->body) > 250 || (isset($simple) && strlen($comment->body) > 73))
                            <button class="font-bold my-2 text-xs uppercase"
                                    @click="isCollapsed = !isCollapsed"
                                    x-text="isCollapsed ? 'Show less' : 'Show more'"
                            ></button>
                            @endif

                            <div class="  flex flex-row   font-semibold pt-3 hover:cursor-pointer select-none">
                                {{--0 both unselected, 1 like button, 2--}}

                                <x-comment-button wire_click="like" class="{{ $liked == 'like' ? 'fill-blue-600' : ''}}" svgIcon="like" text="{{$comment->like_count}}"/>
                                <x-comment-button wire_click="dislike" class="{{ $liked == 'dislike' ? 'fill-blue-600' : ''}}" svgIcon="dislike" text="{{$comment->dislike_count}}"/>

                                @if(!$simple )
                                <span @click="comment = ! comment">
                                            <x-comment-button class="w-4" svgIcon="message" text="Reply"/>
                                </span>
                                @auth
                                @if($comment->owner->id == Auth::user()->creator->id)
                                {{--edit comment button--}}
                                <span @click="editComment = !editComment">
                                            <x-comment-button  svgIcon="pencil" text="Edit"/>
                                        </span>
                                {{--Delete comment button--}}
                                <span @click="confirmPopup = !confirmPopup;comment_id = {{$comment->id}};">
                                            <x-comment-button   svgIcon="bin" text="Delete"/>
                                        </span>
                                @endif

                                @endauth
                                <x-award-button type="comment" object_id="{{$comment->id}}">
                                    <span @click="awardDropdown = true; shadowDiv = true;comment_id = {{$comment->id}}">
                                        <x-comment-button  class="" svgIcon="present" text="Award"/>
                                    </span>
                                </x-award-button>
                                <span @click="shadowDiv = true">
                                    <x-share link="{{route('watch', ['video' => $video->slug, 'comment' => $comment->id])}}"
                                             title="Check out this funny comment on VidGaze - {{$video->title}}">
                                        <x-comment-button  svgIcon="share" text="Share"/>
                                    </x-share>
                                </span>
                                @endif
                                {{-- pinned comment --}}
                                {{--                    @if(isset($pinned))--}}

                                {{--                        <p class="ml-auto mr-2  uppercase text-xs font-bold inline">Pinned</p>--}}
                                {{--                    @endif--}}

                            </div>
                            @if(!$simple)
                            @if ($comment->replies->count() > 0 )
                            <div wire:click="toggleReplies" @click="
                                    if(!idsOfOpenedComments.includes({{$comment->id}})){
                                        idsOfOpenedComments.push({{$comment->id}});
                                    }else{
                                        idsOfOpenedComments.splice(idsOfOpenedComments.indexOf({{$comment->id}}), 1);
                                    }
                                    ">
                                @if(!isset($replies) )
                                <span  class="select-none w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <x-icon name="extend-down" class="fill-blue-600 h-3 my-auto mr-2"/>
                                        <p class="font-bold">View {{$comment->replies->count()}} replies</p>
                                    </span>
                                @else
                                <span class="select-none w-max mt-1 hover:cursor-pointer text-blue-600 dark:text-blue-400 flex justify-start font-semibold pt-2">
                                        <x-icon style="transform: scale(-1, -1);" name="extend-down" class="fill-blue-600 h-3 my-auto mr-2"/>
                                        <p class="font-bold">Minimise</p>
                                    </span>
                                @endif
                            </div>
                            @endif
                            @endif
                        </div>

                    </div>


                </div>

            </div>

    </div>
</template>

