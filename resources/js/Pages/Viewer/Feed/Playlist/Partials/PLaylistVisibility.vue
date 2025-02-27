
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
    },
    editable: {
        type: Boolean,
        default: true
    }
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
        <SelectInput v-if="props.editable" class="dark:bg-zinc-900 w-36" v-model="playlist.visibility" name="visibility" title="Visibility" @update:model-value="value => playlist.visibility = value" :options="visibilityOptions" />
        <div v-if="!editable"  class=" relative cursor-pointer w-full -mb-1.5">
            <span class="capitalize " v-text="playlist.visibility"></span>
        </div>
    </div>
</template>
