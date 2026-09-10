// Auto-scroll the Fava chat message list to the bottom whenever Livewire
// re-renders it (new message sent/received).
document.addEventListener('livewire:load', () => {
    Livewire.hook('message.processed', () => {
        document.querySelectorAll('[data-fava-scroll]').forEach((el) => {
            el.scrollTop = el.scrollHeight;
        });
    });
});
