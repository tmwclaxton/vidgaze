<script setup>
// Sponsor banner for GrantGunner. Copy taken from https://www.grantgunner.org.
defineProps({
    // true when rendered on its own (mobile), false when filling a carousel slot
    standalone: {type: Boolean, default: false},
});
</script>

<template>
    <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-[#006644] via-[#009966] to-[#00b377] text-white overflow-hidden"
         :class="standalone ? 'gap-3 px-4 py-4 rounded-lg shadow-md' : 'gap-6 lg:gap-10 px-24 lg:px-32 py-3'">
        <img src="/images/banners/grantgunner-bucky.webp" alt="Bucky the GrantGunner beaver"
             class="flex-shrink-0 object-contain drop-shadow-lg"
             :class="standalone ? 'h-20 w-20' : 'h-28 w-28 lg:h-36 lg:w-36 xl:h-44 xl:w-44'" />
        <div class="flex flex-col items-start min-w-0" :class="standalone ? 'gap-1.5' : 'gap-2'">
            <span class="text-sm font-semibold uppercase tracking-wider text-white/85">Sponsored by GrantGunner</span>
            <span class="font-bold leading-tight" :class="standalone ? 'text-lg' : 'text-2xl lg:text-3xl xl:text-4xl'">
                Find grants, draft the form, send it when you approve
            </span>
            <span v-if="!standalone" class="hidden xl:block text-base text-white/90 max-w-2xl">
                GrantGunner finds grants that fit your charity, drafts the application from your documents, and can send it once your team approves.
            </span>
            <span class="inline-block mt-1 px-4 py-1.5 rounded-full bg-white text-[#006644] text-sm lg:text-base font-semibold shadow">
                Visit grantgunner.org
            </span>
        </div>
    </div>
</template>
