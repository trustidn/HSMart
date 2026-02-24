<div
    class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl"
    x-data="{}"
    @focus-pos-barcode.window="document.getElementById('pos-barcode-input')?.focus()"
>
    <flux:heading size="xl" class="sr-only lg:not-sr-only">{{ 'Kasir' }}</flux:heading>

    <div class="grid h-full min-h-0 grid-cols-1 gap-4 sm:grid-cols-[1fr_400px] sm:gap-6">
        {{-- Kiri: Pencarian + Grid Produk --}}
        <div class="flex min-h-0 flex-col gap-4">
            <form wire:submit="addByBarcode" class="shrink-0">
                <flux:field class="relative">
                    <flux:input
                        id="pos-barcode-input"
                        wire:model.live.debounce.300ms="barcodeInput"
                        placeholder="{{ 'Cari, pindai barcode atau SKU' }}"
                        icon="magnifying-glass"
                        autocomplete="off"
                        autofocus
                        class="w-full"
                    />
                    @if (strlen(trim($barcodeInput)) >= 1)
                        <div
                            class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            @forelse ($this->productSearchResults as $product)
                                <button
                                    type="button"
                                    wire:click="selectProduct({{ $product->id }})"
                                    class="flex w-full flex-col gap-0.5 px-3 py-2 text-left text-sm transition hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                >
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}{{ $product->barcode ? ' · ' . $product->barcode : '' }}</span>
                                </button>
                            @empty
                                <div class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ 'Produk tidak ditemukan. Ketik nama atau SKU.' }}
                                </div>
                            @endforelse
                        </div>
                    @endif
                    <flux:error name="barcodeInput" />
                </flux:field>
            </form>

            <div class="min-h-0 flex-1 overflow-auto">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse ($this->posProducts as $product)
                        <button
                            type="button"
                            wire:click="selectProduct({{ $product->id }})"
                            class="flex flex-col items-stretch rounded-xl border border-zinc-200 bg-zinc-50 text-left shadow-sm transition hover:border-zinc-300 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:border-zinc-500 dark:hover:bg-zinc-700"
                        >
                            <div class="aspect-square w-full overflow-hidden rounded-t-xl bg-zinc-100 dark:bg-zinc-800">
                                @if ($product->imageUrl())
                                    <img src="{{ $product->imageUrl() }}" alt="" class="h-full w-full object-cover" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-zinc-400">
                                        <flux:icon name="photo" class="h-12 w-12" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col justify-between p-3">
                                <span class="line-clamp-2 font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                <span class="mt-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ number_format($product->sell_price, 0, ',', '.') }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed border-zinc-300 bg-zinc-50/50 py-12 text-center text-zinc-500 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400">
                            {{ 'Belum ada produk aktif. Tambah produk terlebih dahulu.' }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kanan: Keranjang & Pembayaran --}}
        <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:max-h-[calc(100vh-12rem)] sm:overflow-hidden">
            <flux:heading size="lg">{{ 'Pesanan' }}</flux:heading>

            <div class="min-h-0 flex-1 space-y-2 overflow-auto">
                @forelse ($cart as $index => $row)
                    <div
                        wire:key="cart-item-{{ $index }}"
                        class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white py-2 px-3 dark:border-zinc-600 dark:bg-zinc-800"
                    >
                        <div class="min-w-0 flex-1">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['name'] }}</span>
                            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <flux:input
                                    type="number"
                                    min="1"
                                    wire:model.live="cart.{{ $index }}.qty"
                                    class="w-16 text-right text-sm"
                                />
                                <span>× {{ number_format($row['unit_price'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <span class="shrink-0 font-medium">{{ number_format($row['subtotal'], 0, ',', '.') }}</span>
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="removeFromCart({{ $index }})" class="shrink-0 text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400" />
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ 'Keranjang kosong. Pindai atau klik produk.' }}
                    </p>
                @endforelse
            </div>

            <div class="shrink-0 space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:field>
                    <flux:label>{{ 'Nama pelanggan' }}</flux:label>
                    <flux:input wire:model="customerName" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ 'Metode pembayaran' }}</flux:label>
                    <flux:select wire:model="paymentMethod">
                        <option value="cash">{{ 'Tunai' }}</option>
                        <option value="transfer">{{ 'Transfer' }}</option>
                        <option value="other">{{ 'Lainnya' }}</option>
                    </flux:select>
                </flux:field>
                <div class="rounded-lg bg-zinc-200/50 p-3 dark:bg-zinc-800">
                    <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ 'Subtotal' }}</span>
                        <span>{{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                    <flux:heading size="xl" class="mt-1 flex justify-between">
                        <span>{{ 'Total' }}</span>
                        <span>{{ number_format($this->total, 0, ',', '.') }}</span>
                    </flux:heading>
                </div>
                <flux:error name="cart" />
                <flux:error name="checkout" />
                <flux:button
                    type="button"
                    variant="primary"
                    class="w-full py-3 text-base"
                    wire:click="openPaymentModal"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>{{ 'Selesaikan Penjualan' }}</span>
                    <span wire:loading>{{ 'Memproses...' }}</span>
                </flux:button>
                @if (session()->has('sale-completed'))
                    <flux:callout variant="success">{{ 'Penjualan berhasil diselesaikan.' }}</flux:callout>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal konfirmasi pembayaran (overlay dikontrol Livewire) --}}
    @if ($showPaymentModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="payment-modal-title"
        >
            <div
                class="max-h-[90vh] w-full max-w-lg overflow-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                wire:click.stop
            >
                <flux:heading size="lg" id="payment-modal-title">{{ 'Konfirmasi Pembayaran' }}</flux:heading>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ 'Periksa item dan masukkan jumlah bayar. Setelah konfirmasi, struk akan dicetak dan penjualan selesai.' }}</p>

                <div class="mt-4 max-h-48 space-y-2 overflow-auto rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach ($cart as $row)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-900 dark:text-zinc-100">{{ $row['name'] }} × {{ $row['qty'] }}</span>
                            <span>{{ number_format($row['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-100 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex justify-between font-medium">
                        <span>{{ 'Total' }}</span>
                        <span>{{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <flux:field class="mt-4">
                    <flux:label>{{ 'Jumlah bayar' }}</flux:label>
                    <flux:input
                        type="text"
                        inputmode="decimal"
                        wire:model.live="amountPaid"
                        placeholder="0"
                    />
                    <flux:error name="amountPaid" />
                </flux:field>

                @if ((float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $amountPaid)) >= $this->total)
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/30">
                        <div class="flex justify-between font-medium text-green-800 dark:text-green-200">
                            <span>{{ 'Kembalian' }}</span>
                            <span>{{ number_format($this->change, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex gap-2">
                    <flux:button variant="primary" class="flex-1" wire:click="confirmAndPay" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ 'Konfirmasi & Cetak Struk' }}</span>
                        <span wire:loading>{{ 'Memproses...' }}</span>
                    </flux:button>
                    <button
                        type="button"
                        class="flux-btn flux-btn-ghost shrink-0"
                        wire:click="closePaymentModal"
                    >
                        {{ 'Batal' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Struk untuk cetak (hanya tampil saat print) --}}
    @if ($lastReceipt)
        <div
            id="pos-receipt"
            class="receipt-print-only fixed left-0 top-0 z-[100] w-full bg-white p-6 text-black print:block"
            style="display: none;"
            x-data="{}"
            x-init="$nextTick(() => { $el.style.display = 'block'; window.print(); $el.style.display = 'none'; })"
        >
            <div class="mx-auto max-w-xs">
                <div class="border-b border-black pb-2 text-center">
                    <div class="text-lg font-bold">{{ $lastReceipt['store_name'] }}</div>
                    <div class="text-sm">{{ $lastReceipt['date'] }}</div>
                </div>
                <div class="border-b border-black py-2 text-sm">
                    @foreach ($lastReceipt['items'] as $item)
                        <div class="flex justify-between">
                            <span>{{ $item['name'] }} × {{ $item['qty'] }}</span>
                            <span>{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-1 py-2 text-sm">
                    <div class="flex justify-between font-medium">
                        <span>{{ 'Total' }}</span>
                        <span>{{ number_format($lastReceipt['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ 'Dibayar' }}</span>
                        <span>{{ number_format($lastReceipt['amount_paid'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span>{{ 'Kembalian' }}</span>
                        <span>{{ number_format($lastReceipt['change'], 0, ',', '.') }}</span>
                    </div>
                </div>
                @if (!empty($lastReceipt['receipt_footer']))
                    <div class="border-t border-black pt-2 text-center text-xs">{{ $lastReceipt['receipt_footer'] }}</div>
                @endif
            </div>
        </div>
        <style>
            @media print {
                body * { visibility: hidden; }
                #pos-receipt, #pos-receipt * { visibility: visible; }
                #pos-receipt { position: absolute; left: 0; top: 0; width: 100%; display: block !important; }
            }
        </style>
    @endif
</div>
