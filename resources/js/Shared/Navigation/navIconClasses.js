/**
 * Sidebar nav icons — match TopNavBar hamburger when closed (text-cyan-400 + glow).
 * Icons keep cyan when the row is active; hover aligns with menu button (cyan-200).
 */
/** `!text-*` keeps icons cyan when the parent row is active (`text-white` on ResponsiveNavLink). */
export const NAV_SIDEBAR_ICON =
    'h-5 w-5 shrink-0 fill-current !text-cyan-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(34,211,238,0.45)] group-hover:scale-110 group-hover:!text-cyan-200 group-hover:drop-shadow-[0_0_14px_rgba(34,211,238,0.55)]';

export const NAV_SIDEBAR_ICON_FA =
    'h-5 w-5 shrink-0 leading-none !text-cyan-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(34,211,238,0.45)] group-hover:scale-110 group-hover:!text-cyan-200 group-hover:drop-shadow-[0_0_14px_rgba(34,211,238,0.55)]';

/** Labels next to sidebar icons — same cyan as top bar menu; `!` beats active row `text-white`. */
export const NAV_SIDEBAR_LABEL =
    'min-w-0 font-medium !text-cyan-400 transition-colors duration-200 group-hover:!text-cyan-200';
