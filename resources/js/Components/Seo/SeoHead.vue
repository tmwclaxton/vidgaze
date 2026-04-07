<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    /** Plain text or HTML; HTML tags are stripped for meta description. */
    description: { type: String, default: '' },
    image: { type: String, default: null },
    ogType: { type: String, default: 'website' },
    /** Overrides canonical and og:url when set (must be absolute URL). */
    url: { type: String, default: null },
    noindex: { type: Boolean, default: false },
});

const page = usePage();

const seo = computed(() => page.props.seo ?? {});

function stripTags(html) {
    if (!html || typeof html !== 'string') {
        return '';
    }
    return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function truncate(str, max) {
    const t = (str || '').trim();
    if (!t) {
        return '';
    }
    if (t.length <= max) {
        return t;
    }
    return `${t.slice(0, max - 1).trimEnd()}…`;
}

const resolvedDescription = computed(() => {
    const fromProp = stripTags(props.description);
    if (fromProp) {
        return truncate(fromProp, 160);
    }
    const fallback = (seo.value.defaultDescription || '').trim();
    return truncate(fallback, 160);
});

const resolvedOgDescription = computed(() => {
    const fromProp = stripTags(props.description);
    if (fromProp) {
        return truncate(fromProp, 300);
    }
    const fallback = (seo.value.defaultDescription || '').trim();
    return truncate(fallback, 300);
});

const resolvedImage = computed(() => {
    const raw = props.image;
    if (!raw || typeof raw !== 'string') {
        return seo.value.defaultOgImage || '';
    }
    if (/^https?:\/\//i.test(raw)) {
        return raw;
    }
    const base = (seo.value.siteUrl || '').replace(/\/$/, '');
    return `${base}/${raw.replace(/^\//, '')}`;
});

const canonicalUrl = computed(() => {
    if (props.url) {
        return props.url;
    }
    return seo.value.canonicalUrl || '';
});

const siteName = computed(() => seo.value.siteName || 'VidGaze');

const socialTitle = computed(() => {
    const t = (props.title || '').trim();
    if (!t) {
        return siteName.value;
    }
    return `${t} - ${siteName.value}`;
});

const twitterCard = computed(() => (resolvedImage.value ? 'summary_large_image' : 'summary'));

const twitterSiteMeta = computed(() => {
    const h = (seo.value.twitterSite || '').trim();
    if (!h) {
        return null;
    }
    return h.startsWith('@') ? h : `@${h}`;
});
</script>

<template>
    <Head :title="title.trim() || siteName">
        <meta head-key="description" name="description" :content="resolvedDescription" />
        <link head-key="canonical" rel="canonical" :href="canonicalUrl" />
        <meta head-key="og:site_name" property="og:site_name" :content="siteName" />
        <meta head-key="og:type" property="og:type" :content="ogType" />
        <meta head-key="og:title" property="og:title" :content="socialTitle" />
        <meta head-key="og:description" property="og:description" :content="resolvedOgDescription" />
        <meta head-key="og:url" property="og:url" :content="canonicalUrl" />
        <meta
            v-if="resolvedImage"
            head-key="og:image"
            property="og:image"
            :content="resolvedImage"
        />
        <meta head-key="twitter:card" name="twitter:card" :content="twitterCard" />
        <meta head-key="twitter:title" name="twitter:title" :content="socialTitle" />
        <meta head-key="twitter:description" name="twitter:description" :content="resolvedOgDescription" />
        <meta
            v-if="resolvedImage"
            head-key="twitter:image"
            name="twitter:image"
            :content="resolvedImage"
        />
        <meta
            v-if="twitterSiteMeta"
            head-key="twitter:site"
            name="twitter:site"
            :content="twitterSiteMeta"
        />
        <meta
            v-if="noindex"
            head-key="robots"
            name="robots"
            content="noindex, nofollow"
        />
    </Head>
</template>
