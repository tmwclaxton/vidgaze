<script>
export default {
    inheritAttrs: false,
};
</script>

<script setup>
import ResponsiveNavBottomLink from '@/Components/Links/ResponsiveNavBottomLink.vue';
import {
    NAV_SIDEBAR_ICON,
    NAV_SIDEBAR_ICON_FA,
    NAV_SIDEBAR_LABEL,
} from '@/Shared/Navigation/navIconClasses';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <ResponsiveNavBottomLink :href="href" :active="active" class="cursor-pointer" v-bind="$attrs">
        <template v-if="$slots.icon">
            <slot name="icon" :svg-class="NAV_SIDEBAR_ICON" :fa-class="NAV_SIDEBAR_ICON_FA" />
        </template>
        <span class="w-full" :class="NAV_SIDEBAR_LABEL">{{ label }}</span>
    </ResponsiveNavBottomLink>
</template>
