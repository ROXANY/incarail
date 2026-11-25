import { ref } from 'vue';

interface Message {
    text: string;
    color: string;
}

const messages = ref<Message[]>([]);

export function useNotify() {
    function notify(text: string, color = 'info') {
        messages.value.push({ text, color });
    }

    return { messages, notify };
}
