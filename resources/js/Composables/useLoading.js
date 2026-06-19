import { ref } from 'vue';

/**
 * Reusable loading helper to track async execution states.
 * 
 * @param {Function} asyncFn - The async function to wrap.
 * @returns {Object} { isLoading, error, execute }
 */
export function useLoading(asyncFn) {
    const isLoading = ref(false);
    const error = ref(null);

    const execute = async (...args) => {
        isLoading.value = true;
        error.value = null;
        try {
            return await asyncFn(...args);
        } catch (err) {
            error.value = err;
            console.error('Async execution error:', err);
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        isLoading,
        error,
        execute
    };
}
