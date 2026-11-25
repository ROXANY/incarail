import { onUnmounted, ref } from 'vue';

export function useRetryAfter() {
    const seconds = ref(0);
    let timer: number | undefined;

    function start(total: number, onFinish: () => void) {
        cancel();
        seconds.value = total;

        timer = window.setInterval(() => {
            seconds.value--;

            if (seconds.value <= 0) {
                cancel();
                onFinish();
            }
        }, 1000);
    }

    function cancel() {
        window.clearInterval(timer);
        timer = undefined;
        seconds.value = 0;
    }

    onUnmounted(cancel);

    return { seconds, start, cancel };
}
