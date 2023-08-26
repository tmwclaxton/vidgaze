
<script setup>
import {computed, ref} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import TextInput from "@/Components/Inputs/TextInput.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";

const name = "PlaylistName";
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
const editMode = ref(false);

const toggleEditMode = () => {
    editMode.value = !editMode.value;
};

const submit = () => {
    if (!editMode.value) return;
    usePlaylistModalStore().updatePlaylist(props.playlist.id, props.playlist.name, props.playlist.visibility);
    editMode.value = false;
}

</script>
<template>
    <div class="flex flex-row p-1 mt-1 -mb-1 gap-x-2"  v-click-away="submit">
        <text-input v-if="editMode" v-model="playlist.name" @escape="submit"
                    @keydown.enter="submit" @keydown.escape="submit" />
        <p v-if="!editMode" class=" text-xl font-semibold" v-text="playlist.name"></p>
        <font-awesome-icon :icon="['fas', 'pencil']" v-if="props.editable" @click="toggleEditMode" class="my-auto cursor-pointer" />
    </div>
</template>
