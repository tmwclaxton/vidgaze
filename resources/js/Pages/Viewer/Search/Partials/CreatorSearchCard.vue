<template>
    <div class="w-full flex flex-row p-3 rounded-xl">

        <a v-bind:href="'/channel/' + creator.slug" class="flex-shrink-0 flex-grow-0 aspect-square w-24  ">
            <img class="rounded-full" v-bind:src="creator.avatar_url">
            <!--<x-icon name="profile_default"/>-->
        </a>
        <div class="pl-3 sm:pl-8 my-auto justify-center align-middle w-full">
            <div class="flex flex-row ">
                <div class="flex flex-col  ">

                    <a href="/channel/{{creator.slug}}" style="line-height: 15px;"
                       class="text dark:textDark  subheading   ">{{creator.name}}</a>
                    <!--get a str plural package for subscriber count-->
                    <span class="text dark:textDark text-xs mt-1" v-text="creator.subscriber_count + ' Subscriber'">
                    </span>

                </div>
                <div class="hidden xs:flex ml-auto">
                    <!--<livewire:subscribe-button :creator="creator"/>-->
                </div>
            </div>
            <div v-if="creator.sources[0] != null" class="mt-1 space-y-1 md:mb-0 gap-1">
                <!--@foreach(creator->sources as source)-->

                  <!--<x-source-tag :source="source->source_name" class="hidden md:inline-flex"/>-->
                <!--@endforeach-->
                <div v-if="creator.is_live" class="
                    capitalize flex flex-row items-center h-5 p-1 text-xs font-bold leading-tight   rounded-sm w-max
                    text text-zinc-100 dark:text-white   inline-flex bg-red-600 dark:bg-red-700 ">
                    <!--<x-icon name="shorts" class="w-3 h-3 mr-1.5"/>-->
                    <p class="line-clamp-1 ">Live</p>
                </div>
            </div>
            <p class="text dark:textDark text-xs mt-3 ml-2  line-clamp-2 sm:line-clamp-3" v-text="creator.bio"></p>
        </div>
    </div>

</template>

<script>
export default {
    name: "CreatorSearchCard",
    props: {
        creator: {
            type: Object,
            default: null
        }
    }
}
</script>

<style scoped>

</style>
