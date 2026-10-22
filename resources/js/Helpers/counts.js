// The API formats counts as text such as "1.2K Views" or "0 Subscribers".
// A zero usually means the platform did not tell us, so treat it as unknown.
export function knownCount(text) {
    if (text === null || text === undefined) return null;
    const value = String(text).trim();
    if (value === '' || /^0(\.0+)?(\s|$)/.test(value)) return null;
    return value;
}
