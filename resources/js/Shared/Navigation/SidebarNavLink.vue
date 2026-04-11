<script>
export default {
    inheritAttrs: false,
};
</script>

<script setup>
import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import {
    NAV_SIDEBAR_ICON,
    NAV_SIDEBAR_ICON_FA,
    NAV_SIDEBAR_LABEL,
} from '@/Shared/Navigation/navIconClasses';

defineProps({
    href: {
        type: String,
        default: '#',
    },
    active: {
        type: Boolean,
        default: false,
    },
    span: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        required: true,
    },
    /** Merged after `NAV_SIDEBAR_LABEL` (e.g. rail vs expanded text layout). */
    labelClass: {
        type: [String, Array, Object],
        default: '',
    },
});

const emit = defineEmits(['click']);
</script>

<template>
    <ResponsiveNavLink
        :href="href"
        :active="active"
        :span="span"
        class="cursor-pointer"
        v-bind="$attrs"
        @click="(e) => emit('click', e)"
    >
        <slot name="icon" :svg-class="NAV_SIDEBAR_ICON" :fa-class="NAV_SIDEBAR_ICON_FA" />
        <span :class="[NAV_SIDEBAR_LABEL, labelClass]">{{ label }}</span>
    </ResponsiveNavLink>
</template>
