<x-layouts::app title="Subscription Expired">
    <div class="flex h-full w-full flex-1 flex-col items-center justify-center gap-4 rounded-xl">
        <flux:heading size="xl">Subscription Expired</flux:heading>
        <p class="text-neutral-600 dark:text-neutral-400">
            {{ session('message', 'Your subscription has expired. You can still view data but cannot create new sales or purchases.') }}
        </p>
    </div>
</x-layouts::app>
