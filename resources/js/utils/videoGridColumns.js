/**
 * Column counts for the main video grids (VideosRow, InfiniteVideos, Homepage trend section).
 * Must stay aligned with Tailwind classes:
 * grid-cols-2 … ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7
 * and screens in tailwind.config.js (ld: 868px, … 2xl: 1536px).
 */
export function videoGridColumnCount(viewportWidth) {
    const w =
        viewportWidth ??
        (typeof window !== 'undefined' ? window.innerWidth : 1024);

    if (w >= 1536) {
        return 7;
    }
    if (w >= 1280) {
        return 6;
    }
    if (w >= 1024) {
        return 4;
    }
    if (w >= 868) {
        return 3;
    }
    return 2;
}

/**
 * Smallest count >= minCount that fills full rows for the current viewport width.
 */
export function videosCountForCompleteRows(minCount, viewportWidth) {
    const cols = videoGridColumnCount(viewportWidth);
    const floor = Math.max(minCount, cols);
    return Math.ceil(floor / cols) * cols;
}
