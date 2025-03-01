<script setup>
// Your script logic here
</script>

<template>
    <div class="p-6   min-h-screen">
        <Head title="Equity" />

        <div class="flex flex-col items-center justify-center">
            <div class="max-w-2xl  rounded-lg shadow-lg p-8 dark:bg-zinc-800 bg-white">
                <h1 class="text-4xl font-bold mb-6">Equity</h1>
                <p class="text-base leading-relaxed mb-6">
                    VidGaze is yours to own. It's not for the capitalists or for a few very wealthy people
                    to enshitify. It's for <span class="font-semibold ">you</span>. I want you to own a piece.
                    To do that, I need a way to give you a piece. So, there will be a small nominal fee of
                    <span class="font-semibold text-cyan-600 dark:text-cyan-400">1 XMR</span>, which will be used to fund the project and
                    cover server costs. Once (if ever) we reach <span class="font-semibold text-cyan-600 dark:text-cyan-400">10,000 owners</span>,
                    this fee will be removed, the servers & database will be relinquished (hopefully into a federated network),
                    and the project will be <span class="font-semibold ">yours</span> to do with as you please.
                </p>

                <div class="space-y-4 mb-6">
                    <div class="flex items-start">
                        <span class="text-cyan-600 dark:text-cyan-400 mr-3">•</span>
                        <p class="text-base leading-relaxed">
                            Every time someone buys in, the equity gets recalculated:
                            <span class="font-semibold text-cyan-600 dark:text-cyan-400">100 / number of owners = your equity</span>.
                        </p>
                    </div>
                    <div class="flex items-start">
                        <span class="text-cyan-600 dark:text-cyan-400 mr-3">•</span>
                        <p class="text-base leading-relaxed">
                            Equity cannot be sold or transferred. Once you pass away, it goes back to the pool.
                        </p>
                    </div>
                </div>

                <p class="text-base leading-relaxed mb-6">
                    To buy in, send an email to
                    <a href="mailto:vidgaze@gmail.com" class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 font-semibold underline">vidgaze@gmail.com</a>.
                </p>

                <div class="mt-8 text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Together, we can build something better than the capitalists.
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any custom styles here */
</style>
