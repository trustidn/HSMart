<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex items-center gap-2">
                <flux:link :href="route('purchasing.purchases.index')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ __('Purchases') }}
                </flux:link>
            </div>
            <flux:heading size="xl">{{ __('New Purchase') }}</flux:heading>

            <form wire:submit="submit" class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Supplier') }}</flux:label>
                        <flux:select wire:model="supplierId" required>
                            <option value="0">{{ __('Select supplier...') }}</option>
                            @foreach ($this->suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="supplierId" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Purchase date') }}</flux:label>
                        <flux:input type="date" wire:model="purchase_date" required />
                        <flux:error name="purchase_date" />
                    </flux:field>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <flux:heading size="lg">{{ __('Items') }}</flux:heading>
                        <flux:button type="button" variant="ghost" size="sm" icon="plus" wire:click="addRow">
                            {{ __('Add row') }}
                        </flux:button>
                    </div>
                    <flux:error name="items" />
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.row>
                                <flux:table.cell variant="strong" class="w-0">{{ __('Product') }}</flux:table.cell>
                                <flux:table.cell variant="strong" class="w-24">{{ __('Qty') }}</flux:table.cell>
                                <flux:table.cell variant="strong" class="w-32">{{ __('Unit cost') }}</flux:table.cell>
                                <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                            </flux:table.row>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($items as $index => $item)
                                <flux:table.row :key="$index">
                                    <flux:table.cell>
                                        <flux:select wire:model="items.{{ $index }}.product_id" class="min-w-[200px]">
                                            <option value="0">{{ __('Select product...') }}</option>
                                            @foreach ($this->products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:input type="number" wire:model="items.{{ $index }}.qty" min="1" class="w-full" />
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:input type="number" wire:model="items.{{ $index }}.unit_cost" min="0" step="0.01" class="w-full" />
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="removeRow({{ $index }})" />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="flex items-center justify-end gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Total') }}: {{ number_format($this->totalAmount, 0, ',', '.') }}</flux:heading>
                    <flux:button type="submit" variant="primary">
                        {{ __('Create Purchase') }}
                    </flux:button>
                    <flux:button type="button" variant="ghost" :href="route('purchasing.purchases.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </div>
</div>
