<script setup>
import { computed } from 'vue';
import BarList from '@/Components/Stats/BarList.vue';
import LineChart from '@/Components/Stats/LineChart.vue';
import StatTile from '@/Components/Stats/StatTile.vue';

const props = defineProps({ stats: { type: Object, required: true } });

const s = computed(() => props.stats);
const fmt = (n) => new Intl.NumberFormat('en-GB').format(n ?? 0);
const updated = computed(() =>
    new Date(s.value.generatedAt).toLocaleString('en-GB', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' }),
);

const sky = 'rgb(14 165 233)';
const violet = 'rgb(139 92 246)';
const amber = 'rgb(245 158 11)';
const emerald = 'rgb(16 185 129)';

const liveRows = [
    ['pageViews', 'Page views'],
    ['watches', 'Videos opened'],
    ['searches', 'Searches'],
    ['newVideos', 'New videos added'],
    ['signups', 'New accounts'],
    ['likes', 'Likes'],
    ['comments', 'Comments'],
];

const watchedVideos = computed(() =>
    s.value.mostWatchedVideos.map((v) => ({ ...v, sub: v.channel, href: route('watch.show', v.slug) })),
);
const watchedChannels = computed(() =>
    s.value.mostWatchedChannels.map((c) => ({ ...c, href: route('channel.show', c.slug) })),
);
const channelsByVideos = computed(() =>
    s.value.topChannelsByVideos.map((c) => ({ ...c, href: route('channel.show', c.slug) })),
);
</script>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default { layout: AuthenticatedLayout };
</script>

<template>
    <Head title="Site stats" />

    <div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
        <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">VidGaze in numbers</h1>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-300">
                What people are watching, searching and adding on VidGaze. Everything here is a total or a count.
                No names of signed up people, emails or IP addresses are ever shown.
            </p>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Updated {{ updated }}. Numbers refresh every {{ s.cacheMinutes }} minutes.
            </p>
        </div>

        <!-- Headline card -->
        <section class="mt-10 overflow-hidden rounded-3xl border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-violet-50 p-5 shadow-sm sm:p-8 dark:border-sky-900 dark:from-sky-950/40 dark:via-zinc-900 dark:to-zinc-900">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-400">Activity, last {{ s.days }} days</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">
                    <span class="text-sky-600 dark:text-sky-400">{{ fmt(s.live.week.pageViews) }}</span> page views this week
                </h2>
                <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300">Page views, videos opened and searches per day. Known bots are not counted.</p>
            </div>
            <div class="mt-8">
                <LineChart
                    :labels="s.daily.dates"
                    total-suffix="in 30 days"
                    aria-label="Daily page views, videos opened and searches"
                    :series="[
                        { label: 'Page views', data: s.daily.pageViews, color: sky, fill: true },
                        { label: 'Videos opened', data: s.daily.watches, color: violet },
                        { label: 'Searches', data: s.daily.searches, color: amber },
                    ]"
                />
            </div>
        </section>

        <!-- Totals -->
        <section class="mt-12">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">All time totals</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <StatTile label="Videos" :value="s.totals.videos" />
                <StatTile label="Channels" :value="s.totals.channels" />
                <StatTile label="Categories" :value="s.totals.categories" />
                <StatTile label="Registered users" :value="s.totals.users" />
                <StatTile label="Player views" :value="s.totals.playerViews" hint="Counted after a few seconds of play" />
                <StatTile label="Page views" :value="s.totals.pageViews" />
                <StatTile label="Videos opened" :value="s.totals.watches" />
                <StatTile label="Searches" :value="s.totals.searches" />
                <StatTile label="Likes" :value="s.totals.likes" />
                <StatTile label="Comments" :value="s.totals.comments" />
            </div>
        </section>

        <!-- Live activity -->
        <section class="mt-12">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Right now</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <table class="w-full text-left text-base">
                    <thead class="bg-sky-50 text-sm font-semibold text-sky-900 dark:bg-sky-950/40 dark:text-sky-200">
                        <tr>
                            <th class="px-4 py-3 sm:px-6">Activity</th>
                            <th class="px-4 py-3 text-right sm:px-6">Last 24 hours</th>
                            <th class="px-4 py-3 text-right sm:px-6">Last 7 days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <tr v-for="[key, label] in liveRows" :key="key">
                            <td class="px-4 py-3 text-zinc-800 sm:px-6 dark:text-zinc-200">{{ label }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-zinc-900 sm:px-6 dark:text-white">{{ fmt(s.live.day[key]) }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-zinc-900 sm:px-6 dark:text-white">{{ fmt(s.live.week[key]) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Library -->
        <section class="mt-12 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Videos by platform</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Where the videos on VidGaze are hosted.</p>
                <BarList class="mt-5" :rows="s.videosByPlatform" unit="videos" show-share />
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Videos by category</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">How the library is split by topic.</p>
                <BarList class="mt-5" :rows="s.videosByCategory" unit="videos" show-share />
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">New videos and signups</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Videos added to VidGaze and new accounts per day.</p>
                <LineChart class="mt-5" :height="180" total-suffix="in 30 days" aria-label="New videos and signups per day"
                    :labels="s.daily.dates"
                    :series="[
                        { label: 'New videos', data: s.daily.newVideos, color: sky, fill: true },
                        { label: 'New accounts', data: s.daily.signups, color: emerald },
                    ]" />
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">When the videos were made</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Videos by month first published on their platform.</p>
                <LineChart class="mt-5" :height="180" monthly total-suffix="in 12 months" aria-label="Videos by month published"
                    :labels="s.publishedByMonth.months"
                    :series="[{ label: 'Videos published', data: s.publishedByMonth.counts, color: violet, fill: true }]" />
            </div>
        </section>

        <!-- Popular -->
        <section class="mt-12 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Most watched videos</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Times opened in the last {{ s.days }} days.</p>
                <BarList class="mt-5" :rows="watchedVideos" unit="opens" empty="No videos opened yet. This fills in as people watch." />
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Most watched channels</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Videos opened per channel in the last {{ s.days }} days.</p>
                <BarList class="mt-5" :rows="watchedChannels" unit="opens" empty="No channels watched yet. This fills in as people watch." />
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Biggest channels</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">Channels with the most videos on VidGaze.</p>
                <BarList class="mt-5" :rows="channelsByVideos" unit="videos" />
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Popular searches</h2>
                <p class="mt-1 text-base text-zinc-600 dark:text-zinc-300">
                    Only common terms searched at least 3 times in {{ s.days }} days. Anything that looks like an email, link, handle or number is never kept.
                </p>
                <BarList class="mt-5" :rows="s.topSearches" unit="searches" empty="No search term has been used often enough to show yet." />
            </div>
        </section>
    </div>
</template>
