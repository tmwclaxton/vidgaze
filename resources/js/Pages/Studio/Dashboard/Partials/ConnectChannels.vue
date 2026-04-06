<template>
    <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

    <div class="flex flex-row flex-wrap gap-2">
        <template v-for="p in connectablePlatforms" :key="p.id">
            <StudioLinkButton
                v-if="p.link_type === 'oauth' && p.oauth_available"
                :platform="p.id"
                :external_id="p.external_channel_id"
                :text="!p.linked ? 'Sign in with ' + p.label : String(p.external_channel_id)"
                :buttonClasses="buttonClassesFor(p.id)"
            >
                <component :is="iconFor(p.id)" class="w-6 h-6 my-auto"/>
            </StudioLinkButton>

            <div
                v-else-if="p.link_type === 'claim'"
                class="flex flex-col gap-1 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 min-w-[200px]"
            >
                <div class="flex flex-row items-center gap-2">
                    <component :is="iconFor(p.id)" class="w-6 h-6 my-auto"/>
                    <span class="text-sm font-medium">{{ p.label }}</span>
                </div>
                <template v-if="p.linked">
                    <p class="text-xs break-words text-zinc-600 dark:text-zinc-300">{{ p.external_channel_id }}</p>
                    <button
                        type="button"
                        @click="removeClaim(p.id)"
                        class="text-xs text-red-600 dark:text-red-400 text-left"
                    >
                        Remove link
                    </button>
                </template>
                <template v-else>
                    <input
                        v-model="claimInputs[p.id]"
                        type="text"
                        :placeholder="claimPlaceholder(p.id)"
                        class="text-sm rounded border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-900 px-2 py-1"
                    />
                    <button
                        type="button"
                        @click="submitClaim(p.id)"
                        :disabled="claimSubmitting === p.id"
                        class="text-sm font-medium rounded-lg px-3 py-1.5 bg-green-200 hover:bg-green-300 dark:bg-green-900 dark:hover:bg-green-800"
                    >
                        {{ claimSubmitting === p.id ? 'Saving…' : 'Link channel' }}
                    </button>
                </template>
            </div>
        </template>
    </div>
</template>

<script setup>
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
import RumbleIcon from '#icons/rumble.svg';
import OdyseeIcon from '#icons/odysee.svg';
import BitChuteIcon from '#icons/bitchute.svg';
import {computed, reactive, ref} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {useToastStore} from "@/Stores/ToastStore";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";

const authStore = useAuthStore();
const connectablePlatforms = computed(() => authStore.connectable_platforms || []);
const claimInputs = reactive({});
const claimSubmitting = ref(null);

const icons = {
    youtube: YouTubeIcon,
    dailymotion: DailyMotionIcon,
    twitch: TwitchIcon,
    vimeo: VimeoIcon,
    rumble: RumbleIcon,
    odysee: OdyseeIcon,
    bitchute: BitChuteIcon,
};

function iconFor(id) {
    return icons[id] || YouTubeIcon;
}

function buttonClassesFor(id) {
    const map = {
        youtube: "bg-red-200 hover:bg-red-300",
        dailymotion: "bg-zinc-100 hover:bg-zinc-200",
        twitch: "bg-purple-200 hover:bg-purple-300",
        vimeo: "bg-blue-200 hover:bg-blue-300",
    };
    return map[id] || "bg-zinc-100 hover:bg-zinc-200";
}

function claimPlaceholder(id) {
    if (id === 'rumble') {
        return 'Rumble channel slug (from rumble.com/c/...)';
    }
    if (id === 'odysee') {
        return 'Odysee handle (from odysee.com/@YourHandle — enter YourHandle, no @)';
    }
    if (id === 'bitchute') {
        return 'BitChute channel name (from /channel/...)';
    }
    return 'Channel id';
}

function submitClaim(platformId) {
    const channelId = (claimInputs[platformId] || '').trim();
    if (!channelId) {
        useToastStore().add({message: 'Enter a channel id first.', type: 'warning'});
        return;
    }
    claimSubmitting.value = platformId;
    axios.post(route('api.studio.claim', {platform: platformId}), {channel_id: channelId})
        .then(() => {
            useToastStore().add({message: 'Channel linked.', type: 'success'});
            claimInputs[platformId] = '';
            authStore.getUser();
        })
        .catch((err) => {
            const msg = err.response?.data?.message || 'Could not link channel.';
            useToastStore().add({message: msg, type: 'warning'});
        })
        .finally(() => {
            claimSubmitting.value = null;
        });
}

function removeClaim(platformId) {
    useConfirmModalStore().buttonOneText = 'Go Back';
    useConfirmModalStore().buttonTwoText = 'Remove Platform';
    useConfirmModalStore().title = 'Unlink this channel from VidGaze?';
    useConfirmModalStore().show = true;
    useConfirmModalStore().continue = () => {
        axios.delete(route('api.studio.unlink', {platform: platformId}))
            .then(() => {
                authStore.getUser();
                useToastStore().add({message: 'Unlinked.', type: 'success'});
            })
            .catch(() => {
                useToastStore().add({message: 'Unlink failed.', type: 'warning'});
            });
    };
}

const name = 'ConnectChannels';
</script>
