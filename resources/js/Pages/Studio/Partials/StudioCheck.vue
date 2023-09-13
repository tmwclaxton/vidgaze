<template>
    <consistent-content-holder  class="rounded p-2 focus:ring">
        <p class="text-xs font-bold">Before you publish, check the following:</p>
        <div class=" mx-2  ">
            <p class="text-xs font-bold mt-2 my-1">Do kids appear in this video / stream?</p>
            <p class="text-xs mx-1 my-1">Make sure you follow our policies to protect minors from harm,
                exploitation, bullying, and violations of labor law. </p>

            <p class="text-xs font-bold mt-2 my-1">Looking for overall content guidance?</p>
            <p class="text-xs mx-1 my-1">While VidGaze will not to remove content bar sexual activity and violence,
                platforms that host your content
                may do it for other reasons; we recommend you consult each platform's community guidelines to help you
                keep your content up and monetised!
                To clarify, if a verified platform is willing to host your content we won't remove it,
                while not a perfect system, multiple arbiters of truth is better than one.
            </p>

        </div>
    </consistent-content-holder>
</template>

<script>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";

export default {
    name: "StudioCheck",
    components: {ConsistentContentHolder}
}
</script>

