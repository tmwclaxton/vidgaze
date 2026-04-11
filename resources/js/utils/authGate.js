import { useAuthStore } from '@/Stores/AuthStore';
import { useAuthModalStore } from '@/Stores/AuthModalStore';

async function ensureUserLoaded(authStore) {
    if (!localStorage.getItem('token')) {
        return;
    }
    if (authStore.user) {
        return;
    }
    try {
        await authStore.getUser();
    } catch {
        /* token invalid; store cleared in getUser */
    }
}

/**
 * Run callback if the user is authenticated; otherwise open the auth modal and run the callback after success.
 * @param {() => void | Promise<void>} callback
 * @param {{ mode?: 'login' | 'register' }} [options]
 */
export async function requireAuth(callback, options = {}) {
    const { mode = 'login' } = options;
    const authStore = useAuthStore();
    const modal = useAuthModalStore();
    await ensureUserLoaded(authStore);
    if (authStore.user) {
        return callback();
    }
    modal.open(mode, callback);
}

export function openLoginModal(pendingCallback = null) {
    useAuthModalStore().open('login', pendingCallback);
}

export function openRegisterModal(pendingCallback = null) {
    useAuthModalStore().open('register', pendingCallback);
}
