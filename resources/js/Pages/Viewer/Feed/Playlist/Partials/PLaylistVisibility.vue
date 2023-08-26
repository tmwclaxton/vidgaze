
<script setup>
import {computed, ref, watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import TextInput from "@/Components/Inputs/TextInput.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import PlaylistLock from "@/Components/Cards/PlaylistCard/Partials/PlaylistLock.vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";

const name = "PlaylistVisibility";
const props = defineProps({
    playlist: {
        type: Object,
        required: true
    }
});
// computed property that returns true if the playlist is editable
const editable = computed(() => {
    if (!useAuthStore().user) return false;
    // if playlist isn't server made and the user is the owner of the playlistX
    return !props.playlist.server_made && props.playlist.creator.id === useAuthStore().user.creator.id;
});
const visibilityOptions = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
    { value: 'unlisted', label: 'Unlisted' }
];

const submit = () => {
    usePlaylistModalStore().updatePlaylist(props.playlist.id, props.playlist.name, props.playlist.visibility);
}

watch(() => props.playlist.visibility, () => {
    submit();
});

</script>
<template>
    <div class="inline-flex flex-row gap-x-2  items-center"  >
        <PlaylistLock :visibility="playlist.visibility"/>
        <SelectInput v-if="editable" class="dark:bg-zinc-900 w-36" v-model="playlist.visibility" name="visibility" title="Visibility" @update:model-value="value => playlist.visibility = value" :options="visibilityOptions" />
        <div v-if="!editable"  class=" relative cursor-pointer w-full -mb-1.5">
            <span class="capitalize select-none" v-text="playlist.visibility"></span>
        </div>
    </div>
</template>
