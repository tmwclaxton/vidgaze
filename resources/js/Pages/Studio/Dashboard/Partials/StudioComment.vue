<script setup>

const props = defineProps({
    comment: {
        type: Object,
        required: true
    },
});
</script>
<template>
    <div class=" relative h-full w-full my-auto">
        <Link v-bind:href="comment.share_url"  class="absolute top-0 left-0 h-full w-full z-0">

        </Link>
        <div class="pointer-events-none relative pointer-events-none flex flex-row w-full h-full  bg-zinc-100 dark:bg-zinc-800 p-4 px-3 rounded-xl">


            <div class="w-9 mr-3 flex-shrink-0 ">
                <Link :href="route('channel.show', props.comment.owner.slug)" class="pointer-events-auto">
                    <img
                        class=" z-1 relative hover:cursor-pointer inline object-cover w-9 h-9  rounded-full"
                        v-bind:src="props.comment.owner.avatar_url"
                        alt="Profile image"/>
                </Link>
            </div>


            <div class="   flex-grow overflow-hidden">


                <div class=" flex flex-row items-center">
                    <p class="">
                        <Link :href="route('channel.show', props.comment.owner.slug)" class="pointer-events-auto">

                                            <span class="text-sm   font-semibold hover:cursor-pointer  leading-tight  "
                                                  v-text="props.comment.owner.name"/>
                        </Link>
                        <span class="mx-2 text dark:textDark font-bold leading-tight"> · </span>
                        <span class="text-sm font-semibold     leading-tight"
                              v-text="props.comment.created_at"/>
                    </p>
                </div>


                <!--comment body here -->
                <p class=" pr-2 pt-1 break-words   " v-html="props.comment.body"/>


                <div class="  flex flex-row flex-wrap gap-2  font-semibold pt-3 hover:cursor-pointer ">


                </div>

            </div>

        </div>
    </div>

</template>
